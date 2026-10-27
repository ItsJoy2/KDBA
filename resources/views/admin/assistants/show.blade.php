@extends('layouts.app')

@section('title', 'Assistant Profile')

@section('content')
<div class="container-fluid py-3">

    {{-- Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h4 class="mb-1">Assistant Profile</h4>
            <p class="text-muted mb-0">View complete assistant information</p>
        </div>

        <div class="d-flex gap-2">
            <a href="{{ route('assistants.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back
            </a>

            @if(auth()->user()->hasPermission('users.edit'))
                <a href="{{ route('users.edit', $assistant) }}" class="btn btn-primary">
                    <i class="bi bi-pencil me-1"></i> Edit
                </a>
            @endif
        </div>
    </div>

    {{-- Flash Messages --}}
    @foreach(['success' => 'success', 'error' => 'danger'] as $key => $type)
        @if(session($key))
            <div class="alert alert-{{ $type }} alert-dismissible fade show">
                {{ session($key) }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    @endforeach

    {{-- Validation Errors --}}
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

        {{-- Left Column: Profile --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center p-4">

                    @if($photoUrl)
                        <a href="{{ $photoUrl }}" target="_blank" rel="noopener noreferrer">
                            <img
                                src="{{ $photoUrl }}"
                                alt="Assistant Photo"
                                class="rounded-circle mb-3 border"
                                style="width:110px;height:110px;object-fit:cover;"
                            >
                        </a>
                    @else
                        <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex justify-content-center align-items-center mb-3"
                            style="width:110px;height:110px;font-size:36px;">
                            {{ mb_strtoupper(mb_substr($assistant->full_name ?? 'A', 0, 1)) }}
                        </div>
                    @endif

                    <h5 class="mb-1">{{ $assistant->full_name ?? '—' }}</h5>

                    <div class="text-muted mb-3">
                        {{ $assistant->membership_number ?: 'Membership number not assigned' }}
                    </div>

                    <div class="d-flex flex-wrap justify-content-center gap-2 mb-3">
                        <span class="badge text-bg-{{
                            $assistant->approval_status === 'approved'
                                ? 'success'
                                : ($assistant->approval_status === 'pending' ? 'warning' : 'danger')
                        }}">
                            {{ ucfirst($assistant->approval_status ?? 'Unknown') }}
                        </span>

                        <span class="badge text-bg-secondary">
                            {{ ucwords(str_replace('_', ' ', $assistant->member_status ?? 'Unknown')) }}
                        </span>
                    </div>

                    <hr>

                    <div class="text-start">
                        <div class="mb-3">
                            <small class="text-muted d-block">Mobile</small>
                            <div class="fw-medium">{{ $assistant->mobile ?: '—' }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Email</small>
                            <div class="fw-medium text-break">{{ $assistant->email ?: '—' }}</div>
                        </div>

                        <div class="mb-3">
                            <small class="text-muted d-block">Assigned Lawyer</small>
                            <div class="fw-medium">
                                {{ $assistant->lawyer?->full_name ?? 'Not assigned' }}
                            </div>
                        </div>

                        {{-- <div class="mb-3">
                            <small class="text-muted d-block">Lawyer Membership Number</small>
                            <div class="fw-medium">
                                {{ $assistant->lawyer?->membership_number ?? '—' }}
                            </div>
                        </div> --}}

                        <div>
                            <small class="text-muted d-block">Record Created</small>
                            <div class="fw-medium">
                                {{ $assistant->created_at?->format('d M Y, h:i A') ?? '—' }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Professional Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <strong>
                        <i class="bi bi-award me-2"></i>Professional Details
                    </strong>
                </div>

                <div class="card-body">
                    @foreach([
                        'Membership Number' => $assistant->membership_number,
                        'Bar Joining Date' => $assistant->bar_join_date?->format('d M Y'),
                    ] as $label => $value)
                        <div class="mb-3">
                            <small class="text-muted d-block">{{ $label }}</small>
                            <div class="fw-medium">{{ $value ?: '—' }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-xl-8">

            {{-- Approval Management --}}
            @if(auth()->user()->hasPermission('users.approve'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <strong>
                            <i class="bi bi-patch-check me-2"></i>Approval
                        </strong>
                    </div>

                    <div class="card-body">
                        <form
                            method="POST"
                            action="{{ route('users.approve', $assistant) }}"
                            class="d-flex flex-wrap align-items-end gap-3"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="flex-grow-1">
                                <label class="form-label">Approval Status</label>
                                <select name="approval_status" class="form-select" required>
                                    @foreach([
                                        'pending' => 'Pending',
                                        'approved' => 'Approved',
                                        'rejected' => 'Rejected',
                                    ] as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(old('approval_status', $assistant->approval_status) === $value)
                                        >
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

            {{-- Member Status --}}
            @if(auth()->user()->hasPermission('users.change_status'))
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <strong>
                            <i class="bi bi-person-gear me-2"></i>Member Status
                        </strong>
                    </div>

                    <div class="card-body">
                        <form
                            method="POST"
                            action="{{ route('users.change-status', $assistant) }}"
                            class="d-flex flex-wrap align-items-end gap-3"
                        >
                            @csrf
                            @method('PATCH')

                            <div class="flex-grow-1">
                                <label class="form-label">Member Status</label>
                                <select name="member_status" class="form-select" required>
                                    @foreach([
                                        'member' => 'Member',
                                        'inactive' => 'Inactive',
                                        'suspended' => 'Suspended',
                                        'deceased' => 'Deceased',
                                        'life_member' => 'Life Member',
                                        'apprentice' => 'Apprentice',
                                        'lawyer_assistant' => 'Lawyer Assistant',
                                    ] as $value => $label)
                                        <option
                                            value="{{ $value }}"
                                            @selected(old('member_status', $assistant->member_status) === $value)
                                        >
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

            {{-- Personal Details --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <strong>
                        <i class="bi bi-person-lines-fill me-2"></i>Personal Details
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-4">
                        @foreach([
                            'Full Name' => $assistant->full_name,
                            'Email Address' => $assistant->email,
                            'Mobile Number' => $assistant->mobile,
                            'Father’s Name' => $assistant->father_name,
                            'Mother’s Name' => $assistant->mother_name,
                            'Date of Birth' => $assistant->dob?->format('d M Y'),
                            'Gender' => $assistant->gender ? ucfirst($assistant->gender) : null,
                            'Blood Group' => $assistant->blood_group,
                            'NID / Passport Number' => $assistant->nid_passport_number,
                            'Present Address' => $assistant->present_address,
                            'Permanent Address' => $assistant->permanent_address,
                        ] as $label => $value)
                            <div class="col-md-6">
                                <small class="text-muted d-block mb-1">{{ $label }}</small>
                                <div class="fw-medium text-break">
                                    {{ filled($value) ? $value : '—' }}
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- NID Documents --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <strong>
                        <i class="bi bi-card-image me-2"></i>NID Documents
                    </strong>
                </div>

                <div class="card-body">
                    <div class="row g-4">

                        @foreach(['front' => 'NID Front', 'back' => 'NID Back'] as $side => $label)
                            <div class="col-md-6">
                                <h6 class="mb-3">{{ $label }}</h6>

                                @if(!empty($nidImages[$side]))
                                    <a href="{{ $nidImages[$side] }}"
                                    target="_blank"
                                    rel="noopener noreferrer">
                                        <img
                                            src="{{ $nidImages[$side] }}"
                                            alt="{{ $label }}"
                                            class="img-fluid border rounded"
                                            style="width:100%;height:260px;object-fit:contain;"
                                        >
                                    </a>
                                @else
                                    <div class="text-muted">Image not uploaded.</div>
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