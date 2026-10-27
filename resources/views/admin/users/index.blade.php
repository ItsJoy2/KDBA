@extends('layouts.app')

@section('title', 'Members')

@section('content')
    {{-- App Content Header --}}
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Members</h1>
                </div>
                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Members
                            </li>
                        </ol>
                    </nav>
                </div>
            </div>
        </div>
    </div>

    {{-- App Content --}}
    <div class="app-content">
        <div class="container-fluid">

            {{-- Global Feedback Messages --}}
            @include('components.global-feedback')

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">

                        {{-- Card Header --}}
                        <div class="card-header">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-4">
                                    <h3 class="card-title">Member Directory</h3>
                                </div>

                                <div class="col-12 col-md-8">
                                    <form method="GET" action="{{ route('users.index') }}"
                                        class="d-flex flex-wrap justify-content-md-end gap-2">

                                        <div class="input-group input-group-sm w-auto">
                                            <span class="input-group-text">
                                                <i class="bi bi-search" aria-hidden="true"></i>
                                            </span>
                                            <input
                                                type="search"
                                                name="search"
                                                value="{{ request('search') }}"
                                                class="form-control"
                                                placeholder="Name, mobile, email, blood group..."
                                                aria-label="Search members"
                                                style="width: 240px">
                                        </div>

                                        {{-- <select name="approval_status"
                                            class="form-select form-select-sm w-auto"
                                            aria-label="Filter by approval status">
                                            <option value="">All approvals</option>
                                            @foreach (['pending', 'approved', 'rejected'] as $status)
                                                <option value="{{ $status }}"
                                                    @selected(request('approval_status') === $status)>
                                                    {{ ucfirst($status) }}
                                                </option>
                                            @endforeach
                                        </select> --}}

                                        <select name="member_status"
                                            class="form-select form-select-sm w-auto"
                                            aria-label="Filter by member status">
                                            <option value="">All statuses</option>
                                            @foreach ([
                                                'member',
                                                'inactive',
                                                'suspended',
                                                'deceased',
                                                'life_member',
                                                'apprentice',
                                                'lawyer_assistant',
                                            ] as $status)
                                                <option value="{{ $status }}"
                                                    @selected(request('member_status') === $status)>
                                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                                            Filter
                                        </button>

                                        @if (request()->hasAny(['search', 'approval_status', 'member_status', 'per_page']))
                                            <a href="{{ route('users.index') }}"
                                            class="btn btn-sm btn-outline-secondary"
                                            title="Clear filters"
                                            aria-label="Clear filters">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        @endif

                                        @if (auth()->user()->hasPermission('users.create'))
                                            <a href="{{ route('users.create') }}"
                                                class="btn btn-sm btn-success">
                                                <i class="bi bi-person-plus-fill me-1"
                                                    aria-hidden="true"></i>
                                                New member
                                            </a>
                                        @endif
                                    </form>
                                </div>
                            </div>
                        </div>

                        {{-- Card Body --}}
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle m-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Member</th>
                                            <th>Contact</th>
                                            <th>Membership No.</th>
                                            <th>Blood Group</th>
                                            <th>Role</th>
                                            <th>Member Status</th>
                                            <th>Joined</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($users as $member)
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center">
                                                        @if ($member->photo_url)
                                                            <img
                                                                src="{{ $member->photo_url }}"
                                                                alt="{{ $member->full_name }}"
                                                                class="rounded-circle me-2"
                                                                width="32"
                                                                height="32"
                                                                style="object-fit: cover;">
                                                        @else
                                                            <span class="img-size-32 rounded-circle bg-secondary-subtle text-secondary d-inline-flex align-items-center justify-content-center me-2"
                                                                style="width: 32px; height: 32px;">
                                                                <i class="bi bi-person"></i>
                                                            </span>
                                                        @endif

                                                        <div>
                                                            <div class="fw-medium">
                                                                {{ $member->full_name }}
                                                            </div>
                                                            <small class="text-body-secondary">
                                                                #{{ $member->id }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>

                                                <td>
                                                    <div>{{ $member->mobile ?: '—' }}</div>
                                                    <small class="text-body-secondary">
                                                        {{ $member->email ?: 'No email' }}
                                                    </small>
                                                </td>

                                                <td>
                                                    {{ $member->membership_number ?: '—' }}
                                                </td>
                                                <td>
                                                    @if($member->blood_group)
                                                        <span class="badge text-bg-danger">
                                                            {{ $member->blood_group }}
                                                        </span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                <td>
                                                    @forelse($member->roles as $role)
                                                        <span class="badge text-bg-primary mb-1">
                                                            {{ $role->name }}
                                                        </span>
                                                    @empty
                                                        <span class="text-muted">No role assigned</span>
                                                    @endforelse
                                                </td>

                                                {{-- <td>
                                                    @php
                                                        $approvalColors = [
                                                            'pending' => 'warning',
                                                            'approved' => 'success',
                                                            'rejected' => 'danger',
                                                        ];
                                                        $approvalStatus = $member->approval_status;
                                                    @endphp

                                                    <span class="badge text-bg-{{ $approvalColors[$approvalStatus] ?? 'secondary' }}">
                                                        {{ ucfirst($approvalStatus ?? 'unknown') }}
                                                    </span>
                                                </td> --}}

                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'member' => 'success',
                                                            'life_member' => 'success',
                                                            'apprentice' => 'info',
                                                            'lawyer_assistant' => 'info',
                                                            'inactive' => 'secondary',
                                                            'suspended' => 'danger',
                                                            'deceased' => 'dark',
                                                        ];
                                                        $memberStatus = $member->member_status;
                                                    @endphp

                                                    <span class="badge text-bg-{{ $statusColors[$memberStatus] ?? 'secondary' }}">
                                                        {{ ucwords(str_replace('_', ' ', $memberStatus ?? 'unknown')) }}
                                                    </span>
                                                </td>

                                                <td>
                                                    {{ $member->created_at?->format('d M Y') ?? '—' }}
                                                </td>

                                                <td class="text-end pe-3">
                                                    <div class="btn-group btn-group-sm gap-2" role="group"
                                                        aria-label="Member actions">

                                                        @if (auth()->user()->hasPermission('users.view'))
                                                            <a href="{{ route('users.show', $member) }}"
                                                                class="btn btn-outline-primary pt-2"
                                                                aria-label="View {{ $member->full_name }}"
                                                                title="View">
                                                                <i class="bi bi-eye" aria-hidden="true"></i>
                                                            </a>
                                                        @endif

                                                        @if (auth()->user()->hasPermission('users.edit'))
                                                            <a href="{{ route('users.edit', $member) }}"
                                                                class="btn btn-outline-secondary pt-2"
                                                                aria-label="Edit {{ $member->full_name }}"
                                                                title="Edit">
                                                                <i class="bi bi-pencil" aria-hidden="true"></i>
                                                            </a>
                                                        @endif

                                                        @if (auth()->user()->hasPermission('users.approve')
                                                            && $member->approval_status === 'pending')
                                                            <form method="POST"
                                                                action="{{ route('users.approve', $member) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <input type="hidden" name="approval_status"
                                                                    value="approved">
                                                                <button type="submit"
                                                                    class="btn btn-outline-success"
                                                                    title="Approve member"
                                                                    aria-label="Approve {{ $member->full_name }}">
                                                                    <i class="bi bi-check-lg"
                                                                        aria-hidden="true"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        @if (auth()->user()->hasPermission('users.delete')
                                                            && auth()->id() !== $member->id)
                                                            <form method="POST"
                                                                action="{{ route('users.destroy', $member) }}"
                                                                onsubmit="return confirm('Are you sure you want to delete this member?')">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit"
                                                                    class="btn btn-outline-danger"
                                                                    aria-label="Delete {{ $member->full_name }}"
                                                                    title="Delete">
                                                                    <i class="bi bi-trash"
                                                                        aria-hidden="true"></i>
                                                                </button>
                                                            </form>
                                                        @endif
                                                    </div>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-5">
                                                    <i class="bi bi-people fs-2 d-block mb-2 text-body-secondary"></i>
                                                    <div class="fw-medium">No members found</div>
                                                    <small class="text-body-secondary">
                                                        Try changing your search or filters.
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        {{-- Reusable Pagination --}}
                        @include('layouts.partials.__pagination', ['items' => $users,'label' => 'members',
                        ])

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
