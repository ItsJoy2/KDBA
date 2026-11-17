
@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="mb-0">
            <i class="bi bi-cash-stack me-1"></i> Record Income / Expense
        </h4>

        <a href="{{ url()->previous() }}" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left me-1"></i> Back
        </a>
    </div>

    @include('components.global-feedback')

    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header">
                    <strong>Financial Entry Details</strong>
                </div>

                <form method="POST" action="{{ route('admin.transactions.manual.store') }}">
                    @csrf

                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">Category <span class="text-danger">*</span></label>
                            <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">Select category</option>
                                <optgroup label="Income">
                                    @foreach($categories->where('type', 'income') as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="Expense">
                                    @foreach($categories->where('type', 'expense') as $category)
                                        <option value="{{ $category->id }}" @selected(old('category_id') == $category->id)>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" name="amount" step="0.01" min="0.01"
                                       value="{{ old('amount') }}"
                                       class="form-control @error('amount') is-invalid @enderror"
                                       required>
                                @error('amount') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Payment Method / Gateway</label>
                            <select name="payment_gateway_id" class="form-select">
                                <option value="">Cash / Other</option>
                                @foreach($paymentGateways as $gateway)
                                    <option value="{{ $gateway->id }}" @selected(old('payment_gateway_id') == $gateway->id)>
                                        {{ $gateway->name }} - {{ $gateway->account_number }}
                                    </option>
                                @endforeach
                            </select>
                            @error('payment_gateway_id') <div class="text-danger small">{{ $message }}</div> @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description <span class="text-danger">*</span></label>
                            <textarea name="description" rows="4"
                                      class="form-control @error('description') is-invalid @enderror"
                                      required>{{ old('description') }}</textarea>
                            @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="card-footer text-end">
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-circle me-1"></i> Save Transaction
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle me-1"></i> Important</h6>
                <ul class="mb-0 ps-3">
                    <li>Income category দিয়ে আয় এবং Expense category দিয়ে ব্যয় record করো।</li>
                    <li>Manual entry সরাসরি approved হিসেবে সংরক্ষিত হবে।</li>
                    <li>Save করার আগে amount ও description যাচাই করো।</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection
