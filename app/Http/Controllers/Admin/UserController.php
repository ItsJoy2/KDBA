<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Invoice;
use App\Models\PaymentGateway;
use App\Models\Role;
use App\Models\User;
use App\Services\Membership\MembershipFeeService;
use App\Services\Rbac\PermissionService;
use App\Services\UserManagementService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use RuntimeException;
use Throwable;

class UserController extends Controller
{
    private const APPROVAL_STATUSES = [
        'pending',
        'approved',
        'rejected',
    ];

    private const MEMBER_STATUSES = [
        User::MEMBER_STATUS_MEMBER,
        User::MEMBER_STATUS_APPRENTICE,
        User::MEMBER_STATUS_LAWYER_ASSISTANT,
        User::MEMBER_STATUS_INACTIVE,
        User::MEMBER_STATUS_SUSPENDED,
        User::MEMBER_STATUS_DECEASED,
        User::MEMBER_STATUS_OFFICE_STAFF,
    ];

    public function index(Request $request): View
    {
        $actor = $request->user();

        abort_unless(
            $actor?->hasPermission('users.view')
                || $actor?->roles()
                    ->where('slug', 'super-admin')
                    ->exists(),
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
                'member_type',
                'chamber_room_no',
                'chamber_type',
                'lawyer_id',
                'created_at',
            ])
            ->with([
                'lawyer:id,full_name',
                'roles:id,name,slug',
            ]);

        if (! empty($filters['search'])) {
            $search = trim($filters['search']);

            $query->where(function (Builder $builder) use ($search) {
                $builder->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('membership_number', 'like', "%{$search}%")
                    ->orWhere('blood_group', 'like', "%{$search}%");
            });
        }

        if (! empty($filters['approval_status'])) {
            $query->where(
                'approval_status',
                $filters['approval_status']
            );
        }

        if (! empty($filters['member_status'])) {
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

        $paymentGateways = PaymentGateway::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return view('admin.users.index', compact(
            'users',
            'filters',
            'paymentGateways'
        ));
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
            ->where('member_status', User::MEMBER_STATUS_MEMBER)
            ->whereIn('member_type', User::MEMBER_TYPES)
            ->orderBy('full_name')
            ->get([
                'id',
                'full_name',
                'membership_number',
            ]);

        return view('admin.users.create', compact(
            'roles',
            'lawyers'
        ));
    }

    public function store(
        StoreUserRequest $request,
        PermissionService $permissionService
    ): RedirectResponse {
        $actor = $request->user();
        $data = $request->validated();

        abort_unless($actor, 403);

        // Lawyers can only create their own assistants/apprentices.
        if (! $this->canManageAllUsers($actor)) {
            abort_unless(
                $this->isEligibleLawyer($actor),
                403,
                'You are not authorized to create users.'
            );

            abort_unless(
                in_array($data['member_status'] ?? null, [
                    User::MEMBER_STATUS_LAWYER_ASSISTANT,
                    User::MEMBER_STATUS_APPRENTICE,
                ], true),
                403,
                'You can only create your own lawyer assistants or apprentices.'
            );

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

        try {
            if ($request->hasFile('photo')) {
                $data['photo'] = $this->storePrivateFile(
                    $request->file('photo'),
                    'members/photos'
                );

                $uploadedFiles[] = $data['photo'];
            }

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
                $permissionService,
                $canAssignRoles,
                $rolesSubmitted,
                $selectedRoleIds
            ) {
                $user = new User();

                $user->forceFill($data);

                $user->nid_images = $nidImages ?: null;
                $user->created_by = $actor->id;
                $user->updated_by = $actor->id;

                // Clear membership-specific fields for non-members.
                if ($user->member_status !== User::MEMBER_STATUS_MEMBER) {
                    $user->member_type = null;
                    $user->chamber_room_no = null;
                    $user->chamber_type = null;
                }

                $user->status = $this->legacyStatus(
                    $user->member_status
                );

                $user->save();

                // Generate initial admission invoice for members only.
                if ($user->isMember() && $user->member_type) {
                    app(MembershipFeeService::class)
                        ->createRegistrationInvoice($user, $actor->id);
                }

                if ($canAssignRoles && $rolesSubmitted) {
                    $roleIds = $this->resolveRoleIds(
                        $selectedRoleIds,
                        $user->member_status
                    );
                } else {
                    $roleIds = $this->defaultRoleIds(
                        $user->member_status
                    );
                }

                $this->syncUserRoles(
                    $user,
                    $roleIds,
                    $permissionService
                );

                return $user;
            });

            $this->clearUserCaches();

            return redirect()
                ->route('users.index', $user)
                ->with('success', 'User created successfully.');
        } catch (Throwable $exception) {
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
        $actor = $request->user();

        $user = $this->findManageableUser($actor, $user);

        abort_unless(
            $actor?->hasPermission('users.view')
                || $actor?->roles()
                    ->where('slug', 'super-admin')
                    ->exists(),
            403
        );

        $disk = Storage::disk('local');

        $photoUrl = null;

        if ($user->photo && $disk->exists($user->photo)) {
            $photoUrl = 'data:' . ($disk->mimeType($user->photo) ?: 'image/jpeg')
                . ';base64,' . base64_encode($disk->get($user->photo));
        }

        $nidImages = [];

        foreach (['front', 'back'] as $side) {
            $path = data_get($user->nid_images, $side);

            $nidImages[$side] = (
                $path && $disk->exists($path)
            )
                ? 'data:' . ($disk->mimeType($path) ?: 'image/jpeg')
                    . ';base64,' . base64_encode($disk->get($path))
                : null;
        }

        $user->load('roles');

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
        $actor = $request->user();

        $member = $this->findManageableUser($actor, $user);

        abort_unless(
            $actor?->hasPermission('users.edit')
                || $actor?->roles()
                    ->where('slug', 'super-admin')
                    ->exists(),
            403
        );

        $user = $member;

        $disk = Storage::disk('local');

        $photoUrl = null;

        if ($member->photo && $disk->exists($member->photo)) {
            $photoUrl = 'data:' . ($disk->mimeType($member->photo) ?: 'image/jpeg')
                . ';base64,'
                . base64_encode($disk->get($member->photo));
        }

        $nidImages = [];

        foreach (['front', 'back'] as $side) {
            $path = data_get($member->nid_images, $side);

            $nidImages[$side] = (
                $path && $disk->exists($path)
            )
                ? 'data:' . ($disk->mimeType($path) ?: 'image/jpeg')
                    . ';base64,'
                    . base64_encode($disk->get($path))
                : null;
        }

        $roles = Role::query()
            ->where('slug', '!=', 'super-admin')
            ->orderBy('name')
            ->get();

        $member->load('roles');

        $lawyers = User::query()
            ->where('member_status', User::MEMBER_STATUS_MEMBER)
            ->whereIn('member_type', User::MEMBER_TYPES)
            ->where('id', '!=', $member->id)
            ->orderBy('full_name')
            ->get([
                'id',
                'full_name',
                'membership_number',
            ]);

        return view('admin.users.edit', compact(
            'user',
            'member',
            'photoUrl',
            'nidImages',
            'roles',
            'lawyers'
        ));
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

        $data = $request->validated();

        // Lawyers can only update their own assistants/apprentices.
        if (! $this->canManageAllUsers($actor)) {
            abort_unless(
                $target->lawyer_id === $actor->id
                    && in_array($target->member_status, [
                        User::MEMBER_STATUS_LAWYER_ASSISTANT,
                        User::MEMBER_STATUS_APPRENTICE,
                    ], true),
                403,
                'You can only update your own lawyer assistants or apprentices.'
            );

            $data['lawyer_id'] = $actor->id;
        }

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
            if ($request->hasFile('photo')) {
                $newPhoto = $this->storePrivateFile(
                    $request->file('photo'),
                    'members/photos'
                );

                $uploadedFiles[] = $newPhoto;

                if ($target->photo) {
                    $oldFilesToDelete[] = $target->photo;
                }

                $data['photo'] = $newPhoto;
            }

            $existingNidImages = $target->nid_images ?? [];

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
                $target,
                $data,
                $existingNidImages,
                $request,
                $canAssignRoles,
                $rolesSubmitted,
                $selectedRoleIds,
                $permissionService,
                $actor
            ) {
                $lockedUser = User::query()
                    ->whereKey($target->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $lockedUser->forceFill($data);

                $memberStatusChanged = $lockedUser->isDirty('member_status');

                $memberTypeChanged = $lockedUser->isDirty('member_type');

                if (
                    $request->hasFile('nid_images.front')
                    || $request->hasFile('nid_images.back')
                ) {
                    $lockedUser->nid_images = $existingNidImages ?: null;
                }

                // Remove membership-only fields when status is not member.
                if ($lockedUser->member_status !== User::MEMBER_STATUS_MEMBER) {
                    $lockedUser->member_type = null;
                    $lockedUser->chamber_room_no = null;
                    $lockedUser->chamber_type = null;
                }

                $lockedUser->updated_by = $actor->id;

                if ($memberStatusChanged) {
                    $lockedUser->status = $this->legacyStatus(
                        $lockedUser->member_status
                    );
                }

                $lockedUser->save();

                // Create initial admission invoice when a member is assigned.
                if (
                    $lockedUser->isMember()
                    && $lockedUser->member_type
                    && ($memberStatusChanged || $memberTypeChanged)
                ) {
                    app(MembershipFeeService::class)
                        ->createRegistrationInvoice($lockedUser, $actor->id);
                }

                if ($canAssignRoles && $rolesSubmitted) {
                    $roleIds = $this->resolveRoleIds(
                        $selectedRoleIds,
                        $lockedUser->member_status
                    );

                    $this->syncUserRoles(
                        $lockedUser,
                        $roleIds,
                        $permissionService
                    );
                } elseif ($memberStatusChanged) {
                    $roleIds = $this->defaultRoleIds(
                        $lockedUser->member_status
                    );

                    $this->syncUserRoles(
                        $lockedUser,
                        $roleIds,
                        $permissionService
                    );
                }
            });

            // Delete replaced files only after successful DB commit.
            foreach (array_unique($oldFilesToDelete) as $path) {
                Storage::disk('local')->delete($path);
            }

            $this->clearUserCaches();

            return redirect()
                ->route('users.show', $target)
                ->with('success', 'User updated successfully.');
        } catch (Throwable $exception) {
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

    public function changeStatus(
        Request $request,
        User $user,
        PermissionService $permissionService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->hasPermission('users.change_status'),
            403
        );

        $data = $request->validate([
            'member_status' => [
                'required',
                Rule::in(self::MEMBER_STATUSES),
            ],
            'member_type' => [
                'nullable',
                Rule::requiredIf(
                    fn () => $request->input('member_status')
                        === User::MEMBER_STATUS_MEMBER
                ),
                Rule::prohibitedIf(
                    fn () => $request->input('member_status')
                        !== User::MEMBER_STATUS_MEMBER
                ),
                Rule::in(User::MEMBER_TYPES),
            ],
        ]);

        DB::transaction(function () use (
            $user,
            $data,
            $request,
            $permissionService
        ) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->member_status = $data['member_status'];

            if ($data['member_status'] === User::MEMBER_STATUS_MEMBER) {
                $lockedUser->member_type = $data['member_type'];
            } else {
                $lockedUser->member_type = null;
                $lockedUser->chamber_room_no = null;
                $lockedUser->chamber_type = null;
            }

            $lockedUser->status = $this->legacyStatus(
                $lockedUser->member_status
            );

            $lockedUser->updated_by = $request->user()->id;

            $lockedUser->save();

            if (
                $lockedUser->isMember()
                && $lockedUser->member_type
            ) {
                app(MembershipFeeService::class)
                    ->createRegistrationInvoice(
                        $lockedUser,
                        $request->user()->id
                    );
            }

            $roleIds = $this->defaultRoleIds(
                $lockedUser->member_status
            );

            $this->syncUserRoles(
                $lockedUser,
                $roleIds,
                $permissionService
            );
        });

        $this->clearUserCaches();

        return back()->with(
            'success',
            'Member status updated successfully.'
        );
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        abort_unless(
            $request->user()?->hasPermission('users.delete'),
            403
        );

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

    public function updateApproval(
        Request $request,
        User $user
    ): RedirectResponse {
        abort_unless(
            $request->user()?->hasPermission('users.approve'),
            403,
            'You are not authorized to update approval status.'
        );

        $validated = $request->validate([
            'approval_status' => [
                'required',
                Rule::in(self::APPROVAL_STATUSES),
            ],
        ]);

        DB::transaction(function () use ($user, $validated, $request) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $lockedUser->approval_status = $validated['approval_status'];
            $lockedUser->updated_by = $request->user()->id;
            $lockedUser->save();
        });

        $this->clearUserCaches();

        return back()->with(
            'success',
            'Approval status updated successfully.'
        );
    }

    private function storePrivateFile(
        UploadedFile $file,
        string $directory
    ): string {
        $path = $file->store($directory, 'local');

        if (! $path) {
            throw new RuntimeException(
                'Unable to store uploaded file.'
            );
        }

        return $path;
    }

    private function legacyStatus(?string $memberStatus): string
    {
        return match ($memberStatus) {
            User::MEMBER_STATUS_MEMBER => 'Active',
            User::MEMBER_STATUS_INACTIVE => 'Inactive',
            User::MEMBER_STATUS_SUSPENDED => 'Suspended',
            User::MEMBER_STATUS_DECEASED => 'Deceased',
            User::MEMBER_STATUS_APPRENTICE,
            User::MEMBER_STATUS_LAWYER_ASSISTANT,
            User::MEMBER_STATUS_OFFICE_STAFF => 'Active',
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
        return $actor->isMember()
            && in_array($actor->member_type, User::MEMBER_TYPES, true);
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

    private function defaultRoleIds(?string $memberStatus): array
    {
        $roleSlug = $memberStatus
            ? config("member_roles.mapping.{$memberStatus}", 'member')
            : 'general-user';

        $role = Role::query()
            ->where('slug', $roleSlug)
            ->where('slug', '!=', 'super-admin')
            ->first();

        // General Portal User must not automatically receive member role.
        if (! $role && $memberStatus === null) {
            throw new RuntimeException(
                'The general-user role is missing. Please create it first.'
            );
        }

        // Fallback for other member-status mappings.
        if (! $role) {
            $role = Role::query()
                ->where('slug', 'member')
                ->where('slug', '!=', 'super-admin')
                ->first();
        }

        if (! $role) {
            throw new RuntimeException(
                'Default role is missing. Please create it first.'
            );
        }

        return [$role->id];
    }

    private function resolveRoleIds(
        array $selectedRoleIds,
        ?string $memberStatus
    ): array {
        if (empty($selectedRoleIds)) {
            return $this->defaultRoleIds($memberStatus);
        }

        $selectedRoleIds = array_values(array_unique(
            array_map('intval', $selectedRoleIds)
        ));

        $roles = Role::query()
            ->whereIn('id', $selectedRoleIds)
            ->get();

        if (
            $roles->count() !== count($selectedRoleIds)
            || $roles->contains(
                fn (Role $role) => $role->slug === 'super-admin'
            )
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

    public function updateRole(
        Request $request,
        User $user,
        PermissionService $permissionService
    ): RedirectResponse {
        $actor = $request->user();

        abort_unless($actor, 403);

        $isSuperAdmin = $actor->roles()
            ->where('slug', 'super-admin')
            ->where('is_active', true)
            ->exists();

        abort_unless(
            $isSuperAdmin || $actor->hasPermission('users.assign_roles'),
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

    public function unpaidInvoices(User $user): JsonResponse
    {
        $actor = auth()->user();

        abort_unless(
            $actor
                && (
                    $actor->hasPermission('transactions.create')
                    || $actor->hasPermission('invoices.pay')
                ),
            403
        );

        if (! $user->isMember()) {
            return response()->json([
                'message' => 'This user is not eligible for membership payments.',
                'invoices' => [],
            ]);
        }

        $invoices = Invoice::query()
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'overdue'])
            ->whereDoesntHave('transactions', function ($query) {
                $query->whereIn('status', ['pending', 'approved']);
            })
            ->orderBy('due_date')
            ->orderBy('id')
            ->get([
                'id',
                'invoice_number',
                'invoice_type',
                'cycle_key',
                'title',
                'amount',
                'status',
                'due_date',
            ]);

        return response()->json([
            'invoices' => $invoices,
        ]);
    }

    
}
