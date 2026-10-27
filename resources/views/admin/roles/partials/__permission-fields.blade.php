
@php
    $selectedPermissionIds = collect($selectedPermissionIds ?? [])
        ->map(fn ($id) => (int) $id)
        ->all();
@endphp

<div class="row g-3">
    @forelse ($permissionsByModule as $module => $permissions)
        @php
            $moduleKey = \Illuminate\Support\Str::slug($module);
            $modulePermissionIds = $permissions->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            $selectedCount = count(array_intersect(
                $modulePermissionIds,
                $selectedPermissionIds
            ));

            $allSelected = count($modulePermissionIds) > 0
                && $selectedCount === count($modulePermissionIds);
        @endphp

        <div class="col-12 col-xl-6">
            <div class="card h-100 border">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <div>
                        <strong>{{ \Illuminate\Support\Str::headline($module) }}</strong>
                        <span class="badge bg-secondary ms-1">
                            {{ $permissions->count() }}
                        </span>
                    </div>

                    <div class="form-check mb-0">
                        <input
                            class="form-check-input module-select-all"
                            type="checkbox"
                            id="select-all-{{ $moduleKey }}"
                            data-module="{{ $moduleKey }}"
                            @checked($allSelected)
                        >

                        <label
                            class="form-check-label small"
                            for="select-all-{{ $moduleKey }}"
                        >
                            Select All
                        </label>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-2">
                        @foreach ($permissions as $permission)
                            <div class="col-12 col-md-6">
                                <div class="form-check">
                                    <input
                                        class="form-check-input permission-checkbox"
                                        type="checkbox"
                                        name="permissions[]"
                                        value="{{ $permission->id }}"
                                        id="permission-{{ $permission->id }}"
                                        data-module="{{ $moduleKey }}"
                                        @checked(in_array(
                                            (int) $permission->id,
                                            $selectedPermissionIds,
                                            true
                                        ))
                                    >

                                    <label
                                        class="form-check-label"
                                        for="permission-{{ $permission->id }}"
                                    >
                                        {{ \Illuminate\Support\Str::headline($permission->action) }}

                                        @if ($permission->description)
                                            <small
                                                class="d-block text-muted"
                                            >
                                                {{ $permission->description }}
                                            </small>
                                        @endif
                                    </label>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="alert alert-warning">
                No permissions found. Please run the permissions seeder.
            </div>
        </div>
    @endforelse
</div>


@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const moduleCheckboxes = document.querySelectorAll('.module-select-all');
    const permissionCheckboxes = document.querySelectorAll('.permission-checkbox');

    function updateModuleState(moduleKey) {
        const modulePermissions = document.querySelectorAll(
            '.permission-checkbox[data-module="' + moduleKey + '"]'
        );

        const selectAll = document.querySelector(
            '.module-select-all[data-module="' + moduleKey + '"]'
        );

        if (!selectAll) {
            return;
        }

        const checkedCount = Array.from(modulePermissions)
            .filter(checkbox => checkbox.checked).length;

        selectAll.checked =
            modulePermissions.length > 0 &&
            checkedCount === modulePermissions.length;

        selectAll.indeterminate =
            checkedCount > 0 &&
            checkedCount < modulePermissions.length;
    }

    moduleCheckboxes.forEach(function (selectAll) {
        selectAll.addEventListener('change', function () {
            const moduleKey = this.dataset.module;

            document.querySelectorAll(
                '.permission-checkbox[data-module="' + moduleKey + '"]'
            ).forEach(function (checkbox) {
                checkbox.checked = selectAll.checked;
            });

            updateModuleState(moduleKey);
        });
    });

    permissionCheckboxes.forEach(function (checkbox) {
        checkbox.addEventListener('change', function () {
            updateModuleState(this.dataset.module);
        });
    });

    moduleCheckboxes.forEach(function (checkbox) {
        updateModuleState(checkbox.dataset.module);
    });
});
</script>
@endpush
