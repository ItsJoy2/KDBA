
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-tags me-1"></i> Transaction Categories
        </h4>

        @if(auth()->user()->hasPermission('finance_categories.create'))
            <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createCategoryModal">
                <i class="bi bi-plus-circle me-1"></i> Add Category
            </button>
        @endif
    </div>

    @include('components.global-feedback')

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle m-0">
                    <thead>
                        <tr>
                            <th width="5%">#</th>
                            <th>Name</th>
                            <th>Slug</th>
                            <th>Type</th>
                            <th>Transactions</th>
                            <th>Status</th>
                            <th width="150">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($categories as $category)
                            <tr>
                                <td>{{ $categories->firstItem() + $loop->index }}</td>
                                <td>
                                    <strong>{{ $category->name }}</strong>
                                    @if($category->description)
                                        <div class="small text-muted">{{ $category->description }}</div>
                                    @endif
                                </td>
                                <td><code>{{ $category->slug }}</code></td>
                                <td>
                                    <span class="badge {{ $category->type === 'income' ? 'bg-success' : 'bg-danger' }}">
                                        {{ ucfirst($category->type) }}
                                    </span>
                                </td>
                                <td>{{ $category->transactions_count }}</td>
                                <td>
                                    <span class="badge {{ $category->is_active ? 'bg-success' : 'bg-secondary' }}">
                                        {{ $category->is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>
                                    @if(auth()->user()->hasPermission('finance_categories.edit'))
                                        <button
                                            class="btn btn-sm btn-outline-primary"
                                            data-bs-toggle="modal"
                                            data-bs-target="#editCategory{{ $category->id }}">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                    @endif

                                    @if(auth()->user()->hasPermission('finance_categories.delete'))
                                        <form action="{{ route('admin.transaction-categories.destroy', $category) }}"
                                              method="POST" class="d-inline"
                                              onsubmit="return confirm('Are you sure you want to delete this category?')">
                                            @csrf
                                            @method('DELETE')
                                            <button class="btn btn-sm btn-outline-danger"
                                                {{ $category->transactions_count > 0 ? 'disabled' : '' }}>
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </td>
                            </tr>

                            @if(auth()->user()->hasPermission('finance_categories.edit'))
                                <div class="modal fade" id="editCategory{{ $category->id }}" tabindex="-1">
                                    <div class="modal-dialog">
                                        <form method="POST"
                                              action="{{ route('admin.transaction-categories.update', $category) }}"
                                              class="modal-content">
                                            @csrf
                                            @method('PUT')

                                            <div class="modal-header">
                                                <h5 class="modal-title">Edit Category</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>

                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Name</label>
                                                    <input type="text" name="name" class="form-control"
                                                           value="{{ $category->name }}" required maxlength="150">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Slug</label>
                                                    <input type="text" name="slug" class="form-control"
                                                           value="{{ $category->slug }}" required maxlength="180">
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Type</label>
                                                    <select name="type" class="form-select" required>
                                                        <option value="income" @selected($category->type === 'income')>Income</option>
                                                        <option value="expense" @selected($category->type === 'expense')>Expense</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label">Description</label>
                                                    <textarea name="description" class="form-control" rows="2">{{ $category->description }}</textarea>
                                                </div>

                                                <div class="form-check form-switch">
                                                    <input type="hidden" name="is_active" value="0">
                                                    <input class="form-check-input" type="checkbox"
                                                           name="is_active" value="1"
                                                           @checked($category->is_active)>
                                                    <label class="form-check-label">Active</label>
                                                </div>
                                            </div>

                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary">Update Category</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            @endif
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No categories found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @include('layouts.partials.__pagination', ['items' => $categories])
        </div>
    </div>
</div>

@if(auth()->user()->hasPermission('finance_categories.create'))
    <div class="modal fade" id="createCategoryModal" tabindex="-1">
        <div class="modal-dialog">
            <form method="POST" action="{{ route('admin.transaction-categories.store') }}" class="modal-content">
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title">Create Category</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name</label>
                        <input type="text" name="name" class="form-control" required maxlength="150">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Slug</label>
                        <input type="text" name="slug" class="form-control" required maxlength="180">
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="">Select type</option>
                            <option value="income">Income</option>
                            <option value="expense">Expense</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2"></textarea>
                    </div>

                    <input type="hidden" name="is_active" value="1">
                    <div class="form-check">
                        <input type="checkbox" class="form-check-input" checked disabled>
                        <label class="form-check-label">Active</label>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Category</button>
                </div>
            </form>
        </div>
    </div>
@endif
@endsection
