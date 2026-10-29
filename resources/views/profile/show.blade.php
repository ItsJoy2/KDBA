
@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
    @php
        $user = $user ?? auth()->user();

        $status = $user->status ?? 'Unknown';

        $statusClass = match (strtolower((string) $status)) {
            'active', 'life member' => 'success',
            'pending' => 'warning',
            'suspended', 'inactive', 'rejected' => 'danger',
            default => 'secondary',
        };

        $initials = collect(
            preg_split('/\s+/', trim($user->full_name ?? 'User'))
        )
            ->filter()
            ->take(2)
            ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
            ->implode('');

        $photoUrl = $photoUrl ?? null;

        $nidImages = $nidImages ?? [
            'front' => null,
            'back' => null,
        ];
    @endphp

    {{-- Page Header --}}
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div>
                    <h3 class="fw-bold mb-1">My Profile</h3>
                    <p class="text-secondary mb-0">
                        Manage your personal information and account security.
                    </p>
                </div>

                <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i>
                    Dashboard
                </a>
            </div>
        </div>
    </div>

    {{-- Page Content --}}
    <div class="app-content">
        <div class="container-fluid">

            @include('components.global-feedback')

            <div class="row g-4">

                {{-- Left Column --}}
                <div class="col-12 col-xl-4">

                    {{-- Profile Card --}}
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">

                        <div class="bg-primary" style="height: 100px;"></div>

                        <div class="card-body text-center pt-0">

                            {{-- Profile Photo --}}
                            <div
                                class="rounded-circle bg-white p-1 shadow-sm mx-auto
                                       d-flex align-items-center justify-content-center"
                                style="width: 100px; height: 100px; margin-top: -50px;"
                            >
                                @if ($photoUrl)
                                    <img
                                        src="{{ $photoUrl }}"
                                        alt="{{ $user->full_name ?? 'User' }} profile photo"
                                        class="rounded-circle w-100 h-100 object-fit-cover"
                                        loading="lazy"
                                    >
                                @else
                                    <div
                                        class="rounded-circle bg-primary text-white
                                               d-flex align-items-center justify-content-center
                                               w-100 h-100 fs-2 fw-bold"
                                        aria-label="Profile initials"
                                    >
                                        {{ $initials ?: 'U' }}
                                    </div>
                                @endif
                            </div>

                            <h4 class="fw-bold mt-3 mb-1">
                                {{ $user->full_name ?? 'User' }}
                            </h4>

                            <p class="text-secondary text-break mb-2">
                                {{ $user->email ?: 'No email added' }}
                            </p>

                            <span class="badge text-bg-{{ $statusClass }} rounded-pill px-3 py-2">
                                {{ $status }}
                            </span>

                            <hr>

                            {{-- Basic Information --}}
                            <div class="text-start">

                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <span class="text-secondary">Membership No.</span>

                                    <strong class="text-end text-break">
                                        {{ $user->membership_number ?: 'Not assigned' }}
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3 mb-3">
                                    <span class="text-secondary">Mobile</span>

                                    <strong class="text-end text-break">
                                        {{ $user->mobile ?: 'Not added' }}
                                    </strong>
                                </div>

                                <div class="d-flex justify-content-between gap-3">
                                    <span class="text-secondary">Joined</span>

                                    <strong class="text-end">
                                        {{ $user->bar_join_date
                                            ? \Illuminate\Support\Carbon::parse($user->bar_join_date)->format('d M Y')
                                            : 'Not available' }}
                                    </strong>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- Account Security --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4">
                        <div class="card-body">

                            <h6 class="fw-bold mb-3">Account Security</h6>

                            <a
                                href="{{ route('profile.password.edit') }}"
                                class="d-flex align-items-center gap-3 text-decoration-none
                                       text-body p-2 rounded-3"
                            >
                                <span class="bg-primary-subtle text-primary rounded-3 p-3">
                                    <i class="bi bi-key fs-5"></i>
                                </span>

                                <span>
                                    <strong class="d-block">Change Password</strong>
                                    <small class="text-secondary">
                                        Update your account password
                                    </small>
                                </span>

                                <i class="bi bi-chevron-right ms-auto text-secondary"></i>
                            </a>

                            <hr>

                            <a
                                href="{{ route('profile.two-factor.index') }}"
                                class="d-flex align-items-center gap-3 text-decoration-none
                                       text-body p-2 rounded-3"
                            >
                                <span class="bg-success-subtle text-success rounded-3 p-3">
                                    <i class="bi bi-shield-lock fs-5"></i>
                                </span>

                                <span>
                                    <strong class="d-block">Two-Step Verification</strong>
                                    <small class="text-secondary">
                                        Manage login verification
                                    </small>
                                </span>

                                <i class="bi bi-chevron-right ms-auto text-secondary"></i>
                            </a>

                        </div>
                    </div>

                </div>

                {{-- Right Column --}}
                <div class="col-12 col-xl-8">

                    {{-- Personal Information --}}
                    <div class="card border-0 shadow-sm rounded-4">

                        <div class="card-header border-0 p-4">
                            <h5 class="fw-bold mb-1">Personal Information</h5>

                            <p class="text-secondary small mb-0">
                                Information associated with your account.
                            </p>
                        </div>
                        <hr>
                        <div class="card-body px-4 pb-4">

                            <div class="row g-4">

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Full Name
                                    </label>

                                    <div class="form-control bg-body-tertiary text-break">
                                        {{ $user->full_name ?: 'Not provided' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Email Address
                                    </label>

                                    <div class="form-control bg-body-tertiary text-break">
                                        {{ $user->email ?: 'Not provided' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Mobile Number
                                    </label>

                                    <div class="form-control bg-body-tertiary text-break">
                                        {{ $user->mobile ?: 'Not provided' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Membership Number
                                    </label>

                                    <div class="form-control bg-body-tertiary text-break">
                                        {{ $user->membership_number ?: 'Not assigned' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Bar Join Date
                                    </label>

                                    <div class="form-control bg-body-tertiary">
                                        {{ $user->bar_join_date
                                            ? \Illuminate\Support\Carbon::parse($user->bar_join_date)->format('d M Y')
                                            : 'Not available' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Member Status
                                    </label>

                                    <div class="form-control bg-body-tertiary text-break">
                                        {{ $user->member_status ?? 'Not available' }}
                                    </div>
                                </div>

                                <div class="col-12 col-md-6">
                                    <label class="form-label text-secondary small">
                                        Account Status
                                    </label>

                                    <div class="form-control bg-body-tertiary">
                                        {{ $status }}
                                    </div>
                                </div>

                            </div>

                            <div class="alert alert-info mt-4 mb-0">
                                <i class="bi bi-info-circle me-2"></i>
                                If any membership information is incorrect, please contact
                                the Bar Association office.
                            </div>

                        </div>
                    </div>

                    {{-- NID / Passport Images --}}
                    <div class="card border-0 shadow-sm rounded-4 mt-4">

                        <div class="card-header bg-white border-0 p-4">
                            <h5 class="fw-bold mb-1">Identity Documents</h5>

                            <p class="text-secondary small mb-0">
                                Your uploaded NID or passport front and back images.
                            </p>
                        </div>

                        <div class="card-body px-4 pb-4">
                            <div class="row g-4">

                                {{-- Front Image --}}
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-card-image me-2"></i>
                                        Front Side
                                    </h6>

                                    @if (!empty($nidImages['front']))
                                        <a
                                            href="{{ $nidImages['front'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="d-block"
                                            aria-label="View identity document front side"
                                        >
                                            <img
                                                src="{{ $nidImages['front'] }}"
                                                alt="NID or Passport Front Side"
                                                class="img-fluid rounded-3 border w-100"
                                                style="max-height: 320px; object-fit: contain;"
                                                loading="lazy"
                                            >
                                        </a>
                                    @else
                                        <div
                                            class="border rounded-3 p-4 text-center
                                                   text-secondary bg-body-tertiary"
                                        >
                                            <i class="bi bi-card-image fs-1 d-block mb-2"></i>

                                            <p class="mb-0">
                                                Front image not uploaded.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                                {{-- Back Image --}}
                                <div class="col-12 col-md-6">
                                    <h6 class="fw-semibold mb-3">
                                        <i class="bi bi-card-image me-2"></i>
                                        Back Side
                                    </h6>

                                    @if (!empty($nidImages['back']))
                                        <a
                                            href="{{ $nidImages['back'] }}"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                            class="d-block"
                                            aria-label="View identity document back side"
                                        >
                                            <img
                                                src="{{ $nidImages['back'] }}"
                                                alt="NID or Passport Back Side"
                                                class="img-fluid rounded-3 border w-100"
                                                style="max-height: 320px; object-fit: contain;"
                                                loading="lazy"
                                            >
                                        </a>
                                    @else
                                        <div
                                            class="border rounded-3 p-4 text-center
                                                   text-secondary bg-body-tertiary"
                                        >
                                            <i class="bi bi-card-image fs-1 d-block mb-2"></i>

                                            <p class="mb-0">
                                                Back image not uploaded.
                                            </p>
                                        </div>
                                    @endif
                                </div>

                            </div>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>
@endsection
