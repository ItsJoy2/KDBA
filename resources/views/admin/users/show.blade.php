
@extends('layouts.app')

@section('title', 'Member Profile')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Member Profile</h4>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('users.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            @if(auth()->user()->hasPermission('users.edit'))
                <a href="{{ route('users.edit', $user) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>

    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }} alert-dismissible fade show">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    
                @if($photoUrl)
                    <img
                        src="{{ $photoUrl }}"
                        alt="Member Photo"
                        class="rounded-circle mb-3 border"
                        style="width:90px;height:90px;object-fit:cover;"
                    >
                @else
                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex
                                justify-content-center align-items-center mb-3"
                        style="width:90px;height:90px;font-size:30px;">
                        {{ mb_strtoupper(mb_substr($user->full_name ?? 'M', 0, 1)) }}
                    </div>
                @endif
                <h5 class="mb-1">{{ $user->full_name }}</h5>
                    <div class="text-muted mb-3">
                        {{ $user->membership_number ?: 'Membership number not assigned' }}
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-4">
                        <span class="badge text-bg-{{ $user->approval_status === 'approved' ? 'success' : ($user->approval_status === 'pending' ? 'warning' : 'danger') }}">
                            {{ ucfirst($user->approval_status) }}
                        </span>

                        <span class="badge text-bg-secondary">
                            {{ ucwords(str_replace('_', ' ', $user->member_status)) }}
                        </span>
                    </div>

                    <div class="text-start border-top pt-3">
                        <div class="mb-3">
                            <small class="text-muted d-block">Mobile</small>
                            <span>{{ $user->mobile ?: '—' }}</span>
                        </div>
                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <span>{{ $user->email ?: '—' }}</span>
                        </div>
                        <div>
                            <small class="text-muted d-block">Record created</small>
                            <span>{{ $user->created_at?->format('d M Y, h:i A') ?? '—' }}</span>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm mt-4 border-top">
                        <div class="card-header text-start px-0">
                            <strong><i class="bi bi-award me-2"></i>Professional Details</strong>
                        </div>
                        <div class="text-start pt-3">
                            <div class="mb-3">
                                <small class="text-muted d-block">Membership number</small>
                                <span>{{ $user->membership_number ?: '—' }}</span>
                            </div>
                            <div class="mb-3">
                                <small class="text-muted d-block">Bar joining date</small>
                                <span>{{ $user->bar_join_date?->format('d M Y') ?? '—' }}</span>
                            </div>
                            {{-- <div>
                                <small class="text-muted d-block">Sanad number</small>
                                <span>{{ $user->sanad_number ?: '—' }}</span>
                            </div>
                            <div>
                                <small class="text-muted d-block">Sanad number</small>
                                <span>{{ $user->sanad_number ?: '—' }}</span>
                            </div> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-8">
            @if( auth()->user()?->hasPermission('users.approve') || auth()->user()?->roles()->where('slug', 'super-admin')->exists())
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <strong><i class="bi bi-patch-check me-2"></i>Approval</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.approve', $user) }}"
                              class="d-flex flex-wrap align-items-end gap-3">
                            @csrf
                            @method('PATCH')

                            <div class="flex-grow-1">
                                <label class="form-label">Approval status</label>
                                <select name="approval_status" class="form-select" required>
                                    @foreach(['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected'] as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('approval_status', $user->approval_status) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary">
                                Update Approval
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if( auth()->user()?->roles()->where('slug', 'super-admin')->exists() || auth()->user()?->hasPermission('users.assign_roles'))
            <div class="card mt-4">
                <div class="card-header">
                    <h5 class="mb-0">Change User Role</h5>
                </div>

                <div class="card-body">
                    <p class="text-muted small">
                        Select a role to update this user's permissions.
                    </p>

                    <form
                        action="{{ route('users.roles.update', $user) }}"
                        method="POST"
                    >
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="role_id" class="form-label">
                                Assigned Role
                            </label>

                            <select
                                name="role_id"
                                id="role_id"
                                class="form-select"
                                required
                            >
                                <option value="">Select a role</option>

                                @foreach($roles as $role)
                                    <option
                                        value="{{ $role->id }}"
                                        @selected(
                                            $user->roles->contains('id', $role->id)
                                        )
                                    >
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>

                            @error('role_id')
                                <div class="text-danger small mt-1">
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">
                            Update Role
                        </button>
                    </form>
                </div>
            </div>
        @endif

            @if(auth()->user()->hasPermission('users.change_status'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <strong><i class="bi bi-person-gear me-2"></i>Member Status</strong>
                    </div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('users.change-status', $user) }}"
                              class="d-flex flex-wrap align-items-end gap-3">
                            @csrf
                            @method('PATCH')

                            <div class="flex-grow-1">
                                <label class="form-label">Member status</label>
                                <select name="member_status" class="form-select" required>
                                    @foreach([
                                        'member' => 'Member',
                                        'inactive' => 'Inactive',
                                        'suspended' => 'Suspended',
                                        'deceased' => 'Deceased',
                                        'life_member' => 'Life Member',
                                        'apprentice' => 'Apprentice',
                                        'lawyer_assistant' => 'Lawyer Assistant',
                                        'office_staff' => 'Office Staff',
                                    ] as $value => $label)
                                        <option value="{{ $value }}"
                                            @selected(old('member_status', $user->member_status) === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-outline-primary">
                                Update Status
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header py-3">
                    <strong><i class="bi bi-person-lines-fill me-2"></i>Personal Details</strong>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach([
                            'Father’s name' => $user->father_name,
                            'Mother’s name' => $user->mother_name,
                            'Date of birth' => $user->dob?->format('d M Y'),
                            'Gender' => $user->gender ? ucfirst($user->gender) : null,
                            'Blood group' => $user->blood_group,
                            'NID / Passport number' => $user->nid_passport_number,
                            'Present address' => $user->present_address,
                            'Permanent address' => $user->permanent_address,
                        ] as $label => $value)
                            <div class="col-md-6">
                                <small class="text-muted d-block">{{ $label }}</small>
                                <div class="fw-medium">{{ $value ?: '—' }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>


                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header py-3">
                        <strong>
                            <i class="bi bi-card-image me-2"></i>
                            NID Documents
                        </strong>
                    </div>

                    <div class="card-body">
                        <div class="row g-4">
                            @foreach(['front' => 'NID Front', 'back' => 'NID Back'] as $side => $label)
                                <div class="col-md-6">
                                    <h6>{{ $label }}</h6>

                                    @if($nidImages[$side])
                                        <a href="{{ $nidImages[$side] }}"
                                        target="_blank"
                                        rel="noopener noreferrer">
                                            <img
                                                src="{{ $nidImages[$side] }}"
                                                alt="{{ $label }}"
                                                class="img-fluid border rounded"
                                                style="width:100%;max-height:300px;object-fit:contain;"
                                            >
                                        </a>
                                    @else
                                        <p class="text-muted">Image not uploaded.</p>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
        </div>
    </div>
</div>
@endsection
