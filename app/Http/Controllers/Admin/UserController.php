<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Services\UserManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use App\Models\Role;
use App\Services\Rbac\PermissionService;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class UserController extends Controller
{
    private const APPROVAL_STATUSES = [
        'pending',
        'approved',
        'rejected',
    ];

    private const MEMBER_STATUSES = [
        'member',
        'inactive',
        'suspended',
        'deceased',
        'life_member',
        'apprentice',
        'lawyer_assistant',
        'office_staff',
    ];

    public function index(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('users.view')
                || $request->user()?->roles()->where('slug', 'super-admin')->exists(),
            403
        );

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'approval_status' => [
                'nullable',
                Rule::in(self::APPROVAL_STATUSES),
            ],
            'member_status' => [
                'nullable',
                Rule::in(self::MEMBER_STATUSES),
            ],
            'per_page' => ['nullable', 'integer', 'in:10,20,50,100'],
        ]);

        $query = User::query()
            ->whereDoesntHave('roles', function (Builder $roleQuery) {
                $roleQuery->where('slug', 'super-admin');
            })
            ->select([
                'id',
                'full_name',
                'email',
                'mobile',
                'photo',
                'membership_number',
                'blood_group',
                'approval_status',
                'member_status',
                'created_at',
            ])
            ->with([
                'lawyer:id,full_name',
                'roles:id,name,slug',
            ]);

        // Search by name, email, mobile, membership number or blood group
        if (!empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%")
                    ->orWhere('blood_group', 'like', "%{$search}%");
            });
        }

        if (!empty($filters['approval_status'])) {
            $query->where(
                'approval_status',
                $filters['approval_status']
            );
        }

        if (!empty($filters['member_status'])) {
            $query->where(
                'member_status',
                $filters['member_status']
            );
        }

        $users = $query
            ->latest('id')
            ->paginate((int) ($filters['per_page'] ?? 20))
            ->withQueryString();

        $disk = Storage::disk('local');

        $users->getCollection()->transform(function ($member) use ($disk) {
            $path = $member->photo;

            $member->photo_url = (
                is_string($path)
                && $path !== ''
                && $disk->exists($path)
            )
                ? 'data:' . ($disk->mimeType($path) ?: 'image/jpeg')
                    . ';base64,' . base64_encode($disk->get($path))
                : null;

            return $member;
        });

        return view('admin.users.index', compact('users', 'filters'));
    }

    public function create(Request $request): View
    {
        abort_unless(
            $request->user()?->hasPermission('users.create'),
            403
        );

        $roles = Role::query()
            ->where('slug', '!=', 'super-admin')
            ->orderBy('name')
            ->get();

        $lawyers = User::query()
            ->whereIn('member_status', ['member', 'life_member'])
            ->orderBy('full_name')
            ->get(['id', 'full_name', 'membership_number']);

        return view('admin.users.create', compact('roles', 'lawyers'));
    }

    public function store(
        StoreUserRequest $request,
        PermissionService $permissionService
    ): RedirectResponse {
        $actor = $request->user();
        $data = $request->validated();

        // Authorization: restrict lawyer accounts to creating
        // their own lawyer assistants.
        if (! $this->canManageAllUsers($actor)) {
            abort_unless(
                $this->isEligibleLawyer($actor),
                403,
                'You are not authorized to create users.'
            );

            abort_unless(
                ($data['member_status'] ?? null) === ['lawyer_assistant', 'apprentice'],
                403,
                'You can only create your own lawyer assistants.'
            );

            $data['lawyer_id'] = $actor->id;
        }

        // Capture role selection before removing request-only fields.
        // $canAssignRoles = $this->canAssignRoles();

        // $rolesSubmitted = $canAssignRoles
        //     && (bool) ($data['roles_selection_submitted'] ?? false);

        // $selectedRoleIds = $data['roles'] ?? [];

        // unset(
        //     $data['roles'],
        //     $data['roles_selection_submitted']
        // );

        $uploadedFiles = [];

        try {
            // Store the user's photo privately.
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->storePrivateFile(
                    $request->file('photo'),
                    'members/photos'
                );

                $uploadedFiles[] = $data['photo'];
            }

            // Store NID images privately.
            $nidImages = [];

            foreach (['front', 'back'] as $side) {
                if ($request->hasFile("nid_images.{$side}")) {
                    $path = $this->storePrivateFile(
                        $request->file("nid_images.{$side}"),
                        'members/nid'
                    );

                    $nidImages[$side] = $path;
                    $uploadedFiles[] = $path;
                }
            }

            unset($data['nid_images']);

            $user = DB::transaction(function () use (
                $data,
                $nidImages,
                $actor,
                $permissionService
            ) {
                $user = new User();

                // Assign validated user fields.
                $user->forceFill($data);

                $user->nid_images = $nidImages ?: null;
                $user->created_by = $actor->id;
                $user->updated_by = $actor->id;

                // Keep the legacy status column synchronized.
                $user->status = $this->legacyStatus(
                    $user->member_status ?? 'active'
                );

                $user->save();

                $roleIds = $this->defaultRoleIds(
                    $user->member_status ?? 'member'
                );

                $this->syncUserRoles(
                    $user,
                    $roleIds,
                    $permissionService
                );
                return $user;
            });

            // Clear cached user/permission data after a successful commit.
            $this->clearUserCaches();

            return redirect()
                ->route('users.index', $user)
                ->with('success', 'User created successfully.');
        } catch (Throwable $exception) {
            // Remove privately stored files if user creation fails.
            foreach ($uploadedFiles as $path) {
                Storage::disk('local')->delete($path);
            }

            report($exception);

            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                    'nid_images',
                    'photo',
                ]))
                ->with(
                    'error',
                    'Unable to create the user. Please try again.'
                );
        }
    }

    public function show(Request $request, User $user): View
    {
        $target = $this->findManageableUser(
            $request->user(),
            $user
        );

        $user = $target;

        abort_unless(
            $request->user()?->hasPermission('users.view'),
            403
        );

        $disk = Storage::disk('local');

        $photoUrl = null;

        if ($user->photo && $disk->exists($user->photo)) {
            $photoUrl = 'data:' . $disk->mimeType($user->photo)
                . ';base64,' . base64_encode($disk->get($user->photo));
        }

        $nidImages = [];

        foreach (['front', 'back'] as $side) {
            $path = data_get($user->nid_images, $side);

            $nidImages[$side] = $path && $disk->exists($path)
                ? 'data:' . $disk->mimeType($path)
                    . ';base64,' . base64_encode($disk->get($path))
                : null;
        }

        // Load the user's currently assigned roles
        $user->load('roles');

        // Available roles for assignment (exclude Super Admin)
        $roles = Role::query()
            ->where('slug', '!=', 'super-admin')
            ->orderBy('name')
            ->get();

        return view('admin.users.show', compact(
            'user',
            'photoUrl',
            'nidImages',
            'roles'
        ));
    }

    public function edit(Request $request, User $user): View
    {
        $member = $this->findManageableUser(
            $request->user(),
            $user
        );

        abort_unless(
            $request->user()?->hasPermission('users.edit'),
            403
        );

        $disk = Storage::disk('local');

        // Member photo
        $photoUrl = null;

        if ($member->photo && $disk->exists($member->photo)) {
            $photoUrl = 'data:' . $disk->mimeType($member->photo)
                . ';base64,' . base64_encode(
                    $disk->get($member->photo)
                );
        }

        // NID front and back
        $nidImages = [];

        foreach (['front', 'back'] as $side) {
            $path = data_get($member->nid_images, $side);

            $nidImages[$side] = $path && $disk->exists($path)
                ? 'data:' . $disk->mimeType($path)
                    . ';base64,' . base64_encode(
                        $disk->get($path)
                    )
                : null;
        }

        // Keep the variables required by your existing edit form.
        $roles = Role::query()
            ->where('slug', '!=', 'super-admin')
            ->orderBy('name')
            ->get();

        $user->load('roles');

        return view('admin.users.show', compact('user','photoUrl','nidImages','roles'));
    }

    public function update(
        UpdateUserRequest $request,
        User $user,
        PermissionService $permissionService
    ): RedirectResponse {
        $actor = $request->user();

        abort_unless(
            $actor && $actor->hasPermission('users.edit'),
            403
        );

        $target = $this->findManageableUser($actor, $user);
        $user = $target;

        $data = $request->validated();

        // Restrict lawyers to updating their own lawyer assistants.
        if (! $this->canManageAllUsers($actor)) {
            abort_unless(
                $target->member_status === 'lawyer_assistant',
                403,
                'You can only update your own lawyer assistants.'
            );

            // Ignore any lawyer_id submitted by the form.
            $data['lawyer_id'] = $actor->id;
        }

        // Capture role selection before removing request-only fields.
        $canAssignRoles = $this->canAssignRoles();

        $rolesSubmitted = $canAssignRoles
            && (bool) ($data['roles_selection_submitted'] ?? false);

        $selectedRoleIds = $data['roles'] ?? [];

        unset(
            $data['roles'],
            $data['roles_selection_submitted']
        );

        $uploadedFiles = [];
        $oldFilesToDelete = [];

        try {
            // Upload a new profile photo.
            if ($request->hasFile('photo')) {
                $newPhoto = $this->storePrivateFile(
                    $request->file('photo'),
                    'members/photos'
                );

                $uploadedFiles[] = $newPhoto;

                if ($user->photo) {
                    $oldFilesToDelete[] = $user->photo;
                }

                $data['photo'] = $newPhoto;
            }

            // Preserve existing NID images unless replaced.
            $existingNidImages = $user->nid_images ?? [];

            foreach (['front', 'back'] as $side) {
                if ($request->hasFile("nid_images.{$side}")) {
                    $newPath = $this->storePrivateFile(
                        $request->file("nid_images.{$side}"),
                        'members/nid'
                    );

                    $uploadedFiles[] = $newPath;

                    if (! empty($existingNidImages[$side])) {
                        $oldFilesToDelete[] = $existingNidImages[$side];
                    }

                    $existingNidImages[$side] = $newPath;
                }
            }

            unset($data['nid_images']);

            DB::transaction(function () use (
                $user,
                $data,
                $existingNidImages,
                $request,
                $canAssignRoles,
                $rolesSubmitted,
                $selectedRoleIds,
                $permissionService
            ) {
                // Apply validated user fields.
                $user->forceFill($data);

                // Detect status changes before saving.
                $memberStatusChanged = $user->isDirty('member_status');

                // Update NID images only when a new image was uploaded.
                if (
                    $request->hasFile('nid_images.front')
                    || $request->hasFile('nid_images.back')
                ) {
                    $user->nid_images = $existingNidImages ?: null;
                }

                $user->updated_by = $request->user()->id;

                // Keep the legacy status column synchronized.
                if ($memberStatusChanged) {
                    $user->status = $this->legacyStatus(
                        $user->member_status ?? 'active'
                    );
                }

                $user->save();

                // Explicit role selection takes precedence over defaults.
                if ($canAssignRoles && $rolesSubmitted) {
                    $roleIds = $this->resolveRoleIds(
                        $selectedRoleIds,
                        $user->member_status
                    );

                    $this->syncUserRoles(
                        $user,
                        $roleIds,
                        $permissionService
                    );
                } elseif ($memberStatusChanged) {
                    // Automatically update roles when member status changes.
                    $roleIds = $this->defaultRoleIds(
                        $user->member_status
                    );

                    $this->syncUserRoles(
                        $user,
                        $roleIds,
                        $permissionService
                    );
                }
            });

            // Delete replaced files only after the transaction succeeds.
            foreach ($oldFilesToDelete as $path) {
                Storage::disk('local')->delete($path);
            }

            $this->clearUserCaches();

            return redirect()
                ->route('users.show', $user)
                ->with('success', 'User updated successfully.');

        } catch (Throwable $exception) {
            // Remove newly uploaded files if the update fails.
            foreach ($uploadedFiles as $path) {
                Storage::disk('local')->delete($path);
            }

            report($exception);

            return back()
                ->withInput($request->except([
                    'password',
                    'password_confirmation',
                    'nid_images',
                    'photo',
                ]))
                ->with(
                    'error',
                    'Unable to update the user. Please try again.'
                );
        }
    }


    public function approve(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('users.approve'),
            403
        );

        $data = $request->validate([
            'approval_status' => [
                'required',
                Rule::in(['approved', 'rejected']),
            ],
        ]);

        DB::transaction(function () use ($user, $data, $request) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->forceFill([
                'approval_status' => $data['approval_status'],
                'updated_by' => $request->user()->id,
            ])->save();
        });

        $this->clearUserCaches();

        return back()->with(
            'success',
            $data['approval_status'] === 'approved'
                ? 'User approved successfully.'
                : 'User rejected successfully.'
        );
    }

    public function changeStatus(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('users.change_status'),
            403
        );

        $data = $request->validate([
            'member_status' => [
                'required',
                Rule::in(self::MEMBER_STATUSES),
            ],
        ]);

        DB::transaction(function () use ($user, $data, $request) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->forceFill([
                'member_status' => $data['member_status'],
                'status' => $this->legacyStatus($data['member_status']),
                'updated_by' => $request->user()->id,
            ])->save();
        });

        $this->clearUserCaches();

        return back()->with('success', 'Member status updated successfully.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('users.delete'),
            403
        );

        // Prevent accidental deletion of the currently authenticated account.
        abort_if(
            $request->user()->is($user),
            422,
            'You cannot delete your own account.'
        );

        DB::transaction(function () use ($user, $request) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->updated_by = $request->user()->id;
            $lockedUser->save();
            $lockedUser->delete();
        });

        $this->clearUserCaches();

        return redirect()
            ->route('users.index')
            ->with('success', 'User archived successfully.');
    }


    public function updateApproval(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('users.approve'),
            403,
            'You are not authorized to update approval status.'
        );

        $validated = $request->validate([
            'approval_status' => [
                'required',
                Rule::in(['pending', 'approved', 'rejected']),
            ],
        ]);

        $user->approval_status = $validated['approval_status'];
        $user->updated_by = $request->user()->id;
        $user->save();

        return back()->with('success', 'Approval status updated successfully.');
    }

    private function storePrivateFile(UploadedFile $file,string $directory): string {
        $path = $file->store($directory, 'local');

        if (!$path) {
            throw new \RuntimeException('Unable to store uploaded file.');
        }

        return $path;
    }

    private function legacyStatus(string $memberStatus): string
    {
        return match ($memberStatus) {
            'member' => 'Active',
            'inactive' => 'Inactive',
            'suspended' => 'Suspended',
            'deceased' => 'Deceased',
            'life_member' => 'Life Member',
            'apprentice' => 'Active',
            'lawyer_assistant' => 'Active',
            'office_staff' => 'Active',
            default => 'Inactive',
        };
    }


    private function clearUserCaches(): void
    {
        app(UserManagementService::class)
            ->clearDashboardStats();
    }

    private function canManageAllUsers(User $actor): bool
    {
        return $actor->roles()
            ->whereIn('slug', [
                'super-admin',
                'system',
                'general-secretary',
                'office-staff',
            ])
            ->exists();
    }

    private function isEligibleLawyer(User $actor): bool
    {
        return in_array(
            $actor->member_status,
            ['member', 'life_member'],
            true
        );
    }

    private function manageableUsersQuery(User $actor): Builder
    {
        if ($this->canManageAllUsers($actor)) {
            return User::query();
        }

        if ($this->isEligibleLawyer($actor)) {
            return User::query()->forLawyer($actor->id);
        }

        abort(403, 'You are not authorized to manage users.');
    }

    private function findManageableUser(
        User $actor,
        User $target
    ): User {
        return $this->manageableUsersQuery($actor)
            ->whereKey($target->getKey())
            ->firstOrFail();
    }

    private function canAssignRoles(): bool
    {
        return auth()->user()?->hasPermission('users.assign_roles') ?? false;
    }

    private function defaultRoleIds(string $memberStatus): array
    {
        $roleSlug = config(
            "member_roles.mapping.{$memberStatus}",
            'member'
        );

        $role = Role::query()
            ->where('slug', $roleSlug)
            ->where('slug', '!=', 'super-admin')
            ->first();

        // Never leave a new user without a default role.
        if (! $role) {
            $role = Role::query()
                ->where('slug', 'member')
                ->first();
        }

        if (! $role) {
            throw new \RuntimeException(
                'Default Member role is missing. Please create it first.'
            );
        }

        return [$role->id];
    }

    private function resolveRoleIds(
        array $selectedRoleIds,
        string $memberStatus
    ): array {
        // No custom roles selected: use the member-status default.
        if (empty($selectedRoleIds)) {
            return $this->defaultRoleIds($memberStatus);
        }

        $roles = Role::query()
            ->whereIn('id', $selectedRoleIds)
            ->get();

        if (
            $roles->count() !== count(array_unique($selectedRoleIds)) ||
            $roles->contains(fn (Role $role) => $role->slug === 'super-admin')
        ) {
            throw ValidationException::withMessages([
                'roles' => 'One or more selected roles are invalid.',
            ]);
        }

        return $roles->modelKeys();
    }

    private function syncUserRoles(
        User $user,
        array $roleIds,
        PermissionService $permissionService
    ): void {
        $user->roles()->sync($roleIds);

        $permissionService->clearUserCache($user->id);
    }

    public function updateRole(Request $request,User $user,PermissionService $permissionService): RedirectResponse {
        $actor = $request->user();

        $isSuperAdmin = $actor?->roles()->where('slug', 'super-admin')->where('is_active', true)->exists() ?? false;

        abort_unless(
            $isSuperAdmin || $actor?->hasPermission('users.assign_roles'),
            403,
            'You are not authorized to assign roles.'
        );

        $user = $this->findManageableUser($actor, $user);

        $data = $request->validate([
            'role_id' => [
                'required',
                'integer',
                Rule::exists('roles', 'id')
                    ->where('slug', '!=', 'super-admin'),
            ],
        ]);

        $role = Role::findOrFail($data['role_id']);

        // Never allow this endpoint to assign the Super Admin role.
        abort_if(
            $role->slug === 'super-admin',
            403,
            'The Super Admin role cannot be assigned here.'
        );

        DB::transaction(function () use (
            $user,
            $role,
            $actor,
            $permissionService
        ) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->roles()->sync([$role->id]);

            $lockedUser->updated_by = $actor->id;
            $lockedUser->save();

            $permissionService->clearUserCache($lockedUser->id);
        });

        $this->clearUserCaches();

        return redirect()
            ->route('users.show', $user)
            ->with(
                'success',
                "User role updated to {$role->name} successfully."
            );
    }

}
