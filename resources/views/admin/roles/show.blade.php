
@extends('layouts.app')

@section('title', 'Role Details')

@section('content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1">{{ $role->name }}</h4>
            <p class="text-muted mb-0">Role details and assigned permissions.</p>
        </div>

        <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-4">
                    <small class="text-muted d-block">Role Name</small>
                    <strong>{{ $role->name }}</strong>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">Slug</small>
                    <code>{{ $role->slug }}</code>
                </div>

                <div class="col-md-4">
                    <small class="text-muted d-block">Type</small>

                    @if ($role->is_system || $role->slug === 'super-admin')
                        <span class="badge bg-dark">System Role</span>
                    @else
                        <span class="badge bg-success">Custom Role</span>
                    @endif
                </div>

                <div class="col-12">
                    <small class="text-muted d-block">Description</small>
                    {{ $role->description ?: 'No description provided.' }}
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <strong>Assigned Permissions</strong>
            <span class="badge bg-secondary ms-1">
                {{ $role->permissions->count() }}
            </span>
        </div>

        <div class="card-body">
            <div class="row g-3">
                @forelse ($permissionsByModule as $module => $permissions)
                    <div class="col-12 col-xl-6">
                        <div class="border rounded p-3 h-100">
                            <h6 class="mb-3">
                                {{ \Illuminate\Support\Str::headline($module) }}
                            </h6>

                            <div class="d-flex flex-wrap gap-2">
                                @foreach ($permissions as $permission)
                                    <span class="badge bg-light text-dark border">
                                        {{ $permission->slug }}
                                    </span>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="alert alert-warning mb-0">
                            No permissions are assigned to this role.
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    @if (
        auth()->user()->hasPermission('roles.edit')
        && ! $role->is_system
        && $role->slug !== 'super-admin'
    )
        <div class="mt-3 text-end">
            <a href="{{ route('roles.edit', $role) }}" class="btn btn-primary">
                <i class="bi bi-pencil"></i> Edit Role
            </a>
        </div>
    @endif
</div>
@endsection
