<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Permission;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RoleController extends Controller
{
    /**
     * Display all roles.
     */
    public function index(Request $request): View
    {
        $this->authorizePermission($request, 'roles.view');

        $search = trim((string) $request->query('search', ''));

        $roles = Role::query()
            ->select('roles.*')
            ->withCount('permissions')
            ->selectSub(
                DB::table('model_has_roles')
                    ->selectRaw('COUNT(*)')
                    ->whereColumn('model_has_roles.role_id', 'roles.id'),
                'users_count'
            )
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('roles.name', 'like', "%{$search}%")
                        ->orWhere('roles.slug', 'like', "%{$search}%")
                        ->orWhere('roles.description', 'like', "%{$search}%");
                });
            })
            ->orderBy('roles.name')
            ->paginate(15)
            ->withQueryString();

        return view('admin.roles.index', compact('roles', 'search'));
    }

    /**
     * Show the create-role form.
     */
    public function create(Request $request): View
    {
        $this->authorizePermission($request, 'roles.create');

        $permissionsByModule = Permission::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        return view('admin.roles.create', compact('permissionsByModule'));
    }

    /**
     * Store a new role and sync its permissions.
     */
    public function store(StoreRoleRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $permissionIds = $data['permissions'] ?? [];

        if (! $this->canAssignPermissions($request, $permissionIds)) {
            return back()
                ->withInput()
                ->withErrors([
                    'permissions' => 'You cannot assign permissions that you do not have.',
                ]);
        }

        unset($data['permissions']);

        DB::transaction(function () use ($data, $permissionIds, &$role) {
            $role = Role::create([
                'name' => $data['name'],
                'slug' => $this->makeUniqueSlug($data['name']),
                'description' => $data['description'] ?? null,
                'is_system' => false,
            ]);

            $role->permissions()->sync($permissionIds);
        });

        $this->forgetRoleUsersPermissionCache($role->id);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role created successfully.');
    }

    /**
     * Display a role and its assigned permissions.
     */
    public function show(Request $request, Role $role): View
    {
        $this->authorizePermission($request, 'roles.view');

        $role->load('permissions');

        $permissionsByModule = $role->permissions
            ->sortBy([
                ['module', 'asc'],
                ['action', 'asc'],
            ])
            ->groupBy('module');

        return view(
            'admin.roles.show',
            compact('role', 'permissionsByModule')
        );
    }

    /**
     * Show the edit-role form.
     */
    public function edit(Request $request, Role $role): View
    {
        $this->authorizePermission($request, 'roles.edit');
        abort_if($this->isProtectedRole($role), 403, 'This system role cannot be edited.');

        $role->load('permissions');

        $permissionsByModule = Permission::query()
            ->orderBy('module')
            ->orderBy('action')
            ->get()
            ->groupBy('module');

        $selectedPermissionIds = $role->permissions
            ->pluck('id')
            ->map(fn ($id) => (int) $id)
            ->all();

        return view('admin.roles.edit', compact(
            'role',
            'permissionsByModule',
            'selectedPermissionIds'
        ));
    }

    /**
     * Update a role and synchronize its permissions.
     */
    public function update(
        UpdateRoleRequest $request,
        Role $role
    ): RedirectResponse {
        abort_if($this->isProtectedRole($role), 403, 'This system role cannot be edited.');

        $data = $request->validated();
        $permissionIds = $data['permissions'] ?? [];

        if (! $this->canAssignPermissions($request, $permissionIds)) {
            return back()
                ->withInput()
                ->withErrors([
                    'permissions' => 'You cannot assign permissions that you do not have.',
                ]);
        }

        unset($data['permissions']);

        DB::transaction(function () use ($role, $data, $permissionIds) {
            $role->update([
                'name' => $data['name'],
                'slug' => $this->makeUniqueSlug(
                    $data['name'],
                    $role->id
                ),
                'description' => $data['description'] ?? null,
            ]);

            // An empty selection clears the role's existing permissions.
            $role->permissions()->sync($permissionIds);
        });

        $this->forgetRoleUsersPermissionCache($role->id);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role updated successfully.');
    }

    /**
     * Delete a role if it is not protected and has no assigned users.
     */
    public function destroy(Request $request, Role $role): RedirectResponse
    {
        $this->authorizePermission($request, 'roles.delete');

        if ($this->isProtectedRole($role)) {
            return back()->with(
                'error',
                'The Super Admin/system role cannot be deleted.'
            );
        }

        $assignedUserIds = $this->getRoleUserIds($role->id);

        if ($assignedUserIds->isNotEmpty()) {
            return back()->with(
                'error',
                'This role cannot be deleted because users are assigned to it.'
            );
        }

        DB::transaction(function () use ($role) {
            $role->permissions()->detach();
            $role->delete();
        });

        // Also clear any stale cache associated with the deleted role.
        $this->forgetUserPermissionCaches($assignedUserIds);

        return redirect()
            ->route('roles.index')
            ->with('success', 'Role deleted successfully.');
    }

    /**
     * Check whether the current user has a required permission.
     */
    private function authorizePermission(
        Request $request,
        string $permission
    ): void {
        abort_unless(
            $request->user()?->hasPermission($permission),
            403,
            'You are not authorized to perform this action.'
        );
    }

    /**
     * Protect system roles and Super Admin from modifications.
     */
    private function isProtectedRole(Role $role): bool
    {
        return $role->slug === 'super-admin';
    }

    /**
     * Generate a unique slug for the role.
     */
    private function makeUniqueSlug(
        string $name,
        ?int $ignoreRoleId = null
    ): string {
        $baseSlug = Str::slug($name);

        if ($baseSlug === '') {
            $baseSlug = 'role';
        }

        $slug = $baseSlug;
        $suffix = 2;

        while (
            Role::query()
                ->where('slug', $slug)
                ->when(
                    $ignoreRoleId,
                    fn ($query) => $query->where('id', '!=', $ignoreRoleId)
                )
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $suffix;
            $suffix++;
        }

        return $slug;
    }

    /**
     * Prevent non-super-admin users from granting permissions
     * they do not personally possess.
     */
    private function canAssignPermissions(
        Request $request,
        array $permissionIds
    ): bool {
        $user = $request->user();

        if (! $user) {
            return false;
        }

        $isSuperAdmin = DB::table('model_has_roles')
            ->join('roles', 'roles.id', '=', 'model_has_roles.role_id')
            ->where('model_has_roles.user_id', $user->id)
            ->where('roles.slug', 'super-admin')
            ->exists();

        if ($isSuperAdmin) {
            return true;
        }

        if (empty($permissionIds)) {
            return true;
        }

        $permissions = Permission::query()
            ->whereIn('id', $permissionIds)
            ->get(['id', 'slug']);

        foreach ($permissions as $permission) {
            if (! $user->hasPermission($permission->slug)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get user IDs assigned to a role.
     */
    private function getRoleUserIds(int $roleId)
    {
        return DB::table('model_has_roles')
            ->where('role_id', $roleId)
            ->pluck('user_id')
            ->unique()
            ->values();
    }

    /**
     * Invalidate permission caches for users assigned to a role.
     */
    private function forgetRoleUsersPermissionCache(int $roleId): void
    {
        $userIds = $this->getRoleUserIds($roleId);

        $this->forgetUserPermissionCaches($userIds);
    }

    /**
     * Clear the cache key used by the existing PermissionService.
     */
    private function forgetUserPermissionCaches($userIds): void
    {
        foreach ($userIds as $userId) {
            Cache::forget("rbac:user:{$userId}:permissions");
        }
    }
}
