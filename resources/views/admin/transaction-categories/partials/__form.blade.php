@foreach($categories as $category)

    @if(
        !$category->is_system &&
        auth()->user()->hasPermission('finance_categories.edit')
    )

        <div
            class="modal fade"
            id="editCategory{{ $category->id }}"
            tabindex="-1"
            aria-labelledby="editCategoryLabel{{ $category->id }}"
            aria-hidden="true"
        >
            <div class="modal-dialog modal-dialog-centered">
                <form
                    method="POST"
                    action="{{ route('admin.transaction-categories.update', $category) }}"
                    class="modal-content"
                >
                    @csrf
                    @method('PUT')

                    <div class="modal-header">
                        <h5
                            class="modal-title"
                            id="editCategoryLabel{{ $category->id }}"
                        >
                            <i class="bi bi-pencil-square me-1"></i>
                            Edit Category
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
                                value="{{ $category->name }}"
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
                                value="{{ $category->slug }}"
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
                                <option
                                    value="income"
                                    @selected($category->type === 'income')
                                >
                                    Income
                                </option>

                                <option
                                    value="expense"
                                    @selected($category->type === 'expense')
                                >
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
                            >{{ $category->description }}</textarea>
                        </div>

                        {{-- Active --}}
                        <div class="form-check form-switch">
                            <input
                                type="hidden"
                                name="is_active"
                                value="0"
                            >

                            <input
                                class="form-check-input"
                                type="checkbox"
                                name="is_active"
                                value="1"
                                id="categoryActive{{ $category->id }}"
                                @checked($category->is_active)
                            >

                            <label
                                class="form-check-label"
                                for="categoryActive{{ $category->id }}"
                            >
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
                            Update Category
                        </button>
                    </div>

                </form>
            </div>
        </div>

    @endif

@endforeach