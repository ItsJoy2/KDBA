@extends('layouts.app')

@section('title', 'Role Management')

@section('content')
    {{-- App Content Header --}}
    <div class="app-content-header">
        <div class="container-fluid">
            <div class="row">
                <div class="col-sm-6">
                    <h1 class="mb-0 fs-3">Role Management</h1>
                </div>

                <div class="col-sm-6">
                    <nav aria-label="breadcrumb">
                        <ol class="breadcrumb float-sm-end">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard') }}">Home</a>
                            </li>
                            <li class="breadcrumb-item active" aria-current="page">
                                Role Management
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

            @include('components.global-feedback')

            <div class="row">
                <div class="col-12">
                    <div class="card mb-4">

                        {{-- Card Header --}}
                        <div class="card-header">
                            <div class="row g-2 align-items-center">

                                <div class="col-12 col-md-4">
                                    <h3 class="card-title">Role Directory</h3>
                                </div>

                                <div class="col-12 col-md-8">
                                    <form method="GET"
                                        action="{{ route('roles.index') }}"
                                        class="d-flex flex-wrap justify-content-md-end gap-2">
                                        {{-- Create Role --}}
                                        @if (auth()->user()->hasPermission('roles.create'))
                                            <a href="{{ route('roles.create') }}"
                                                class="btn btn-sm btn-primary">
                                                <i class="bi bi-plus-circle me-1"
                                                    aria-hidden="true"></i>
                                                New role
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
                                            <th class="ps-3">#</th>
                                            <th>Role</th>
                                            <th>Permissions</th>
                                            <th>Assigned Users</th>
                                            <th>Type</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($roles as $role)

                                            @php
                                                $protected = $role->is_system
                                                    || $role->slug === 'super-admin';
                                            @endphp

                                            <tr>
                                                {{-- Serial Number --}}
                                                <td class="ps-3">
                                                    {{ $roles->firstItem() + $loop->index }}
                                                </td>

                                                {{-- Role Information --}}
                                                <td>
                                                    <div class="d-flex align-items-center">

                                                        <div>
                                                            <div class="fw-medium">
                                                                {{ $role->name }}
                                                            </div>
                                                        </div>

                                                    </div>
                                                </td>

                                                {{-- Permissions Count --}}
                                                <td>
                                                    <span class="badge text-bg-info">
                                                        {{ $role->permissions_count }}
                                                    </span>
                                                </td>

                                                {{-- Assigned Users --}}
                                                <td>
                                                    <span class="badge text-bg-secondary">
                                                        {{ $role->users_count }}
                                                    </span>
                                                </td>

                                                {{-- Role Type --}}
                                                <td>
                                                    @if ($protected)
                                                        <span class="badge text-bg-dark">
                                                            System Role
                                                        </span>
                                                    @else
                                                        <span class="badge text-bg-success">
                                                            Custom Role
                                                        </span>
                                                    @endif
                                                </td>

                                                {{-- Actions --}}
                                                <td class="text-end pe-3">
                                                    <div class="btn-group btn-group-sm gap-2"
                                                        role="group"
                                                        aria-label="Role actions">

                                                        {{-- Show: Available for all roles --}}
                                                        @if (auth()->user()->hasPermission('roles.view'))
                                                            <a href="{{ route('roles.show', $role) }}"
                                                                class="btn btn-outline-primary pt-2"
                                                                title="View">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Edit: Custom roles only --}}
                                                        @if (! $protected && auth()->user()->hasPermission('roles.edit'))
                                                            <a href="{{ route('roles.edit', $role) }}"
                                                                class="btn btn-outline-secondary pt-2"
                                                                title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Delete: Custom roles only --}}
                                                        @if (! $protected && auth()->user()->hasPermission('roles.delete'))
                                                            <form method="POST"
                                                                action="{{ route('roles.destroy', $role) }}"
                                                                onsubmit="return confirm('Are you sure you want to delete this role?')">
                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                    class="btn btn-outline-danger"
                                                                    title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                    </div>
                                                </td>
                                            </tr>

                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-5">
                                                    <i class="bi bi-shield-lock fs-2 d-block mb-2 text-body-secondary"
                                                        aria-hidden="true"></i>

                                                    <div class="fw-medium">
                                                        No roles found
                                                    </div>

                                                    <small class="text-body-secondary">
                                                        Try changing your search.
                                                    </small>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>

                                </table>
                            </div>
                        </div>

                        {{-- Reusable Pagination --}}
                        @include('layouts.partials.__pagination', [
                            'items' => $roles,
                            'label' => 'roles',
                        ])

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
