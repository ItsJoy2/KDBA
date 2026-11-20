
@extends('layouts.app')

@section('title', 'Transaction Categories')

@section('content')
<div class="container-fluid">

    {{-- Page Header --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h4 class="mb-0">
            <i class="bi bi-tags me-1"></i>
            Transaction Categories
        </h4>

        @if(auth()->user()->hasPermission('finance_categories.create'))
            <button
                type="button"
                class="btn btn-primary"
                data-bs-toggle="modal"
                data-bs-target="#createCategoryModal"
            >
                <i class="bi bi-plus-circle me-1"></i>
                Add Category
            </button>
        @endif
    </div>

    @include('components.global-feedback')

    {{-- Categories Table --}}
    <div class="card">
        <div class="card-header">
            <div class="d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">
                    <i class="bi bi-list-ul me-1"></i>
                    Category List
                </h5>

                <span class="badge bg-secondary">
                    Total: {{ $categories->total() }}
                </span>
            </div>
        </div>

        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover table-bordered align-middle m-0">

                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th >Type</th>
                            <th>Transactions</th>
                            <th>Status</th>
                            <th width="130" class="text-center">Actions</th>
                        </tr>
                    </thead>

                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                {{-- Serial --}}
                                <td>
                                    {{ $categories->firstItem() + $loop->index }}
                                </td>

                                {{-- Name --}}
                                <td>
                                    <div class="fw-semibold">
                                        {{ $category->name }}
                                    </div>

                                    @if($category->description)
                                        <div class="small text-muted">
                                            {{ $category->description }}
                                        </div>
                                    @endif
                                </td>

                                {{-- Slug --}}
                                <td>
                                    <code>{{ $category->slug }}</code>
                                </td>

                                {{-- Type --}}
                                <td>
                                    @if($category->type === 'income')
                                        <span class="badge bg-success">
                                            <i class="bi bi-arrow-down-circle me-1"></i>
                                            Income
                                        </span>
                                    @else
                                        <span class="badge bg-danger">
                                            <i class="bi bi-arrow-up-circle me-1"></i>
                                            Expense
                                        </span>
                                    @endif
                                </td>

                                {{-- Transaction Count --}}
                                <td>
                                    <span class="badge bg-info text-dark">
                                        {{ $category->transactions_count }}
                                    </span>
                                </td>

                                {{-- Status --}}
                                <td>
                                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="text-center">
                                    @if($category->is_system)
                                        <span
                                            class="text-muted small"
                                            title="System categories cannot be modified"
                                        >
                                            <i class="bi bi-lock-fill me-1"></i>
                                            Locked
                                        </span>
                                    @else

                                        {{-- Edit --}}
                                        @if(auth()->user()->hasPermission('finance_categories.edit'))
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-outline-primary"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editCategory{{ $category->id }}"
                                                title="Edit Category"
                                            >
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                        @endif

                                        {{-- Delete --}}
                                        @if(auth()->user()->hasPermission('finance_categories.delete'))
                                            <form
                                                action="{{ route('admin.transaction-categories.destroy', $category) }}"
                                                method="POST"
                                                class="d-inline"
                                                onsubmit="return confirm('Are you sure you want to delete this category?')"
                                            >
                                                @csrf
                                                @method('DELETE')

                                                <button
                                                    type="submit"
                                                    class="btn btn-sm btn-outline-danger"
                                                    {{ $category->transactions_count > 0 ? 'disabled' : '' }}
                                                    title="{{ $category->transactions_count > 0 ? 'Category is already used by transactions' : 'Delete Category' }}"
                                                >
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        @endif

                                        @if(
                                            !auth()->user()->hasPermission('finance_categories.edit') &&
                                            !auth()->user()->hasPermission('finance_categories.delete')
                                        )
                                            <span class="text-muted small">No actions</span>
                                        @endif

                                    @endif
                                </td>
                            </tr>

                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox fs-3 d-block mb-2"></i>
                                    No transaction categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>

                </table>
            </div>

            {{-- Pagination --}}
            <div class="mt-3">
                @include('layouts.partials.__pagination', ['items' => $categories])
            </div>
        </div>
    </div>
</div>


{{-- ========================================================= --}}
{{-- CREATE CATEGORY MODAL --}}
{{-- ========================================================= --}}

@if(auth()->user()->hasPermission('finance_categories.create'))
    <div
        class="modal fade"
        id="createCategoryModal"
        tabindex="-1"
        aria-labelledby="createCategoryModalLabel"
        aria-hidden="true"
    >
        <div class="modal-dialog modal-dialog-centered">
            <form
                method="POST"
                action="{{ route('admin.transaction-categories.store') }}"
                class="modal-content"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="createCategoryModalLabel">
                        <i class="bi bi-plus-circle me-1"></i>
                        Create Transaction Category
                    </h5>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal"
                        aria-label="Close"
                    ></button>
                </div>

                <div class="modal-body">

                    {{-- Name --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Category Name <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="name"
                            class="form-control"
                            value="{{ old('name') }}"
                            placeholder="Enter category name"
                            maxlength="150"
                            required
                        >
                    </div>

                    {{-- Slug --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Slug <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="slug"
                            class="form-control"
                            value="{{ old('slug') }}"
                            placeholder="example-category"
                            maxlength="180"
                            required
                        >
                    </div>

                    {{-- Type --}}
                    <div class="mb-3">
                        <label class="form-label">
                            Category Type <span class="text-danger">*</span>
                        </label>

                        <select name="type" class="form-select" required>
                            <option value="">Select Type</option>

                            <option value="income" @selected(old('type') === 'income')>
                                Income
                            </option>

                            <option value="expense" @selected(old('type') === 'expense')>
                                Expense
                            </option>
                        </select>
                    </div>

                    {{-- Description --}}
                    <div class="mb-3">
                        <label class="form-label">Description</label>

                        <textarea
                            name="description"
                            class="form-control"
                            rows="3"
                            placeholder="Enter category description"
                        >{{ old('description') }}</textarea>
                    </div>

                    {{-- Active --}}
                    <input type="hidden" name="is_active" value="1">

                    <div class="form-check">
                        <input
                            type="checkbox"
                            class="form-check-input"
                            checked
                            disabled
                        >

                        <label class="form-check-label">
                            Active
                        </label>
                    </div>

                </div>

                <div class="modal-footer">
                    <button
                        type="button"
                        class="btn btn-secondary"
                        data-bs-dismiss="modal"
                    >
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-circle me-1"></i>
                        Save Category
                    </button>
                </div>

            </form>
        </div>
    </div>
@endif

@include('admin.transaction-categories.partials.__form')

@endsection
