
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
                                    <form method="GET"
                                        action="{{ route('users.index') }}"
                                        class="d-flex flex-wrap justify-content-md-end gap-2">

                                        {{-- Search --}}
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

                                        {{-- Member Status Filter --}}
                                        <select name="member_status"
                                            class="form-select form-select-sm w-auto"
                                            aria-label="Filter by member status">

                                            <option value="">All statuses</option>

                                            <option value="__general__"
                                                @selected(request('member_status') === '__general__')>
                                                General Portal User
                                            </option>

                                            @foreach ([
                                                'member',
                                                'apprentice',
                                                'lawyer_assistant',
                                                'inactive',
                                                'suspended',
                                                'deceased',
                                                'office_staff',
                                            ] as $status)
                                                <option value="{{ $status }}"
                                                    @selected(request('member_status') === $status)>
                                                    {{ ucwords(str_replace('_', ' ', $status)) }}
                                                </option>
                                            @endforeach
                                        </select>

                                        {{-- Filter Button --}}
                                        <button type="submit" class="btn btn-sm btn-primary">
                                            <i class="bi bi-funnel me-1" aria-hidden="true"></i>
                                            Filter
                                        </button>

                                        {{-- Clear Filters --}}
                                        @if (request()->hasAny([
                                            'search',
                                            'approval_status',
                                            'member_status',
                                            'per_page',
                                        ]))
                                            <a href="{{ route('users.index') }}"
                                                class="btn btn-sm btn-outline-secondary"
                                                title="Clear filters"
                                                aria-label="Clear filters">
                                                <i class="bi bi-arrow-counterclockwise"></i>
                                            </a>
                                        @endif

                                        {{-- Create Member --}}
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
                                            <th>Chamber</th>
                                            <th>Joined</th>
                                            <th class="text-end pe-3">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @forelse ($users as $member)
                                            <tr>

                                                {{-- Member Information --}}
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

                                                {{-- Contact --}}
                                                <td>
                                                    <div>{{ $member->mobile ?: '—' }}</div>

                                                    <small class="text-body-secondary">
                                                        {{ $member->email ?: 'No email' }}
                                                    </small>
                                                </td>

                                                {{-- Membership Number --}}
                                                <td>
                                                    {{ $member->membership_number ?: '—' }}
                                                </td>

                                                {{-- Blood Group --}}
                                                <td>
                                                    @if ($member->blood_group)
                                                        <span class="badge text-bg-danger">
                                                            {{ $member->blood_group }}
                                                        </span>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                {{-- Roles --}}
                                                <td>
                                                    @forelse ($member->roles as $role)
                                                        <span class="badge text-bg-primary mb-1">
                                                            {{ $role->name }}
                                                        </span>
                                                    @empty
                                                        <span class="text-muted">
                                                            No role assigned
                                                        </span>
                                                    @endforelse
                                                </td>

                                                {{-- Member Status --}}
                                                <td>
                                                    @php
                                                        $statusColors = [
                                                            'member' => 'success',
                                                            'apprentice' => 'info',
                                                            'lawyer_assistant' => 'info',
                                                            'inactive' => 'secondary',
                                                            'suspended' => 'danger',
                                                            'deceased' => 'dark',
                                                            'office_staff' => 'primary',
                                                        ];

                                                        $memberStatus = $member->member_status;

                                                        $statusLabel = $memberStatus
                                                            ? ucwords(str_replace('_', ' ', $memberStatus))
                                                            : 'General Portal User';
                                                    @endphp

                                                    <span class="badge text-bg-{{ $statusColors[$memberStatus] ?? 'secondary' }}">
                                                        {{ $statusLabel }}
                                                    </span>

                                                    @if ($member->member_type)
                                                        <div class="small text-body-secondary mt-1">
                                                            {{ $member->member_type === 'mother_member'
                                                                ? 'Mother Member'
                                                                : 'Sub Member' }}
                                                        </div>
                                                    @endif
                                                </td>

                                                {{-- Chamber Information --}}
                                                <td>
                                                    @if ($member->chamber_type)
                                                        <div class="fw-medium">
                                                            {{ ucwords(str_replace('_', ' ', $member->chamber_type)) }}
                                                        </div>

                                                        <small class="text-body-secondary">
                                                            Room: {{ $member->chamber_room_no ?: '—' }}
                                                        </small>
                                                    @else
                                                        —
                                                    @endif
                                                </td>

                                                {{-- Joined Date --}}
                                                <td>
                                                    {{ $member->created_at?->format('d M Y') ?? '—' }}
                                                </td>

                                                {{-- Actions --}}
                                                <td class="text-end pe-3 align-middle">
                                                    <div class="d-flex align-items-center justify-content-end gap-2 flex-nowrap"
                                                        role="group"
                                                        aria-label="Member actions">

                                                        {{-- View --}}
                                                        @if (auth()->user()->hasPermission('users.view'))
                                                            <a href="{{ route('users.show', $member) }}"
                                                                class="btn btn-sm btn-outline-primary member-action-btn"
                                                                aria-label="View {{ $member->full_name }}"
                                                                title="View">
                                                                <i class="bi bi-eye"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Edit --}}
                                                        @if (auth()->user()->hasPermission('users.edit'))
                                                            <a href="{{ route('users.edit', $member) }}"
                                                                class="btn btn-sm btn-outline-secondary member-action-btn"
                                                                aria-label="Edit {{ $member->full_name }}"
                                                                title="Edit">
                                                                <i class="bi bi-pencil"></i>
                                                            </a>
                                                        @endif

                                                        {{-- Approve --}}
                                                        @if (
                                                            auth()->user()->hasPermission('users.approve')
                                                            && $member->approval_status === 'pending'
                                                        )
                                                            <form method="POST"
                                                                action="{{ route('users.approve', $member) }}"
                                                                class="d-inline-flex m-0">

                                                                @csrf
                                                                @method('PATCH')

                                                                <input type="hidden"
                                                                    name="approval_status"
                                                                    value="approved">

                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-success member-action-btn"
                                                                    title="Approve member"
                                                                    aria-label="Approve {{ $member->full_name }}">
                                                                    <i class="bi bi-check-lg"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        {{-- Delete --}}
                                                        @if (
                                                            auth()->user()->hasPermission('users.delete')
                                                            && auth()->id() !== $member->id
                                                        )
                                                            <form method="POST"
                                                                action="{{ route('users.destroy', $member) }}"
                                                                class="d-inline-flex m-0 js-delete-confirm"
                                                                data-delete-message="Are you sure you want to delete {{ $member->full_name }}? This action cannot be undone.">

                                                                @csrf
                                                                @method('DELETE')

                                                                <button type="submit"
                                                                    class="btn btn-sm btn-outline-danger member-action-btn"
                                                                    aria-label="Delete {{ $member->full_name }}"
                                                                    title="Delete">
                                                                    <i class="bi bi-trash"></i>
                                                                </button>
                                                            </form>
                                                        @endif

                                                        {{-- Record Payment --}}
                                                        @if (
                                                            auth()->user()->hasPermission('transactions.create')
                                                            && $member->member_status === 'member'
                                                        )
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-success member-action-btn js-open-payment-modal"
                                                                data-user-id="{{ $member->id }}"
                                                                data-user-name="{{ $member->full_name }}"
                                                                data-invoices-url="{{ route('users.unpaid-invoices', $member) }}"
                                                                title="Record Payment"
                                                                aria-label="Record payment for {{ $member->full_name }}">
                                                                <i class="bi bi-cash-coin"></i>
                                                            </button>
                                                        @endif

                                                    </div>
                                                </td>

                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="9" class="text-center py-5">
                                                    <i class="bi bi-people fs-2 d-block mb-2 text-body-secondary"></i>

                                                    <div class="fw-medium">
                                                        No members found
                                                    </div>

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
                        @include('layouts.partials.__pagination', [
                            'items' => $users,
                            'label' => 'members',
                        ])

                    </div>
                </div>
            </div>

        </div>
    </div>
@endsection

{{-- Payment Modal --}}
@include('admin.users.partials.__pay')

@if (auth()->user()->hasPermission('transactions.create'))
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const modalElement = document.getElementById('adminPaymentModal');

            if (!modalElement) {
                return;
            }

            const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
            const form = document.getElementById('adminPaymentForm');

            const invoiceSelect = document.getElementById('paymentInvoice');
            const amountInput = document.getElementById('paymentAmount');
            const amountValue = document.getElementById('paymentAmountValue');
            const submitButton = document.getElementById('submitPaymentBtn');

            if (!form || !invoiceSelect || !amountInput || !amountValue || !submitButton) {
                return;
            }

            function clearErrors() {
                form.querySelectorAll('.is-invalid').forEach(element => {
                    element.classList.remove('is-invalid');
                });

                form.querySelectorAll('[data-error]').forEach(element => {
                    element.textContent = '';
                });
            }

            function showError(field, message) {
                const input = form.querySelector(`[name="${field}"]`);
                const error = form.querySelector(`[data-error="${field}"]`);

                if (input) {
                    input.classList.add('is-invalid');
                }

                if (error) {
                    error.textContent = message;
                }
            }

            function resetInvoiceSelection() {
                invoiceSelect.innerHTML = '<option value="">Select an invoice</option>';
                amountInput.value = '';
                amountValue.value = '';
            }

            document.querySelectorAll('.js-open-payment-modal').forEach(button => {
                button.addEventListener('click', async function () {
                    clearErrors();
                    form.reset();
                    resetInvoiceSelection();

                    document.getElementById('paymentMemberName').textContent =
                        this.dataset.userName;

                    const url = this.dataset.invoicesUrl;

                    invoiceSelect.innerHTML =
                        '<option value="">Loading invoices...</option>';

                    modal.show();

                    try {
                        const response = await fetch(url, {
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            },
                            credentials: 'same-origin'
                        });

                        const result = await response.json();

                        if (!response.ok) {
                            throw new Error(result.message || 'Unable to load invoices.');
                        }

                        resetInvoiceSelection();

                        if (!result.invoices || !result.invoices.length) {
                            invoiceSelect.innerHTML =
                                '<option value="">No unpaid invoice available</option>';

                            return;
                        }

                        result.invoices.forEach(invoice => {
                            const option = document.createElement('option');

                            option.value = invoice.id;
                            option.dataset.amount = invoice.amount;

                            const type = invoice.invoice_type === 'yearly_renewal'
                                ? 'Yearly Renewal'
                                : 'Admission';

                            option.textContent =
                                `${invoice.invoice_number} — ${type} (${invoice.cycle_key}) — ${invoice.amount}`;

                            invoiceSelect.appendChild(option);
                        });

                    } catch (error) {
                        resetInvoiceSelection();

                        const option = document.createElement('option');
                        option.value = '';
                        option.textContent = error.message;

                        invoiceSelect.appendChild(option);
                    }
                });
            });

            invoiceSelect.addEventListener('change', function () {
                const selectedOption = this.options[this.selectedIndex];
                const amount = selectedOption?.dataset.amount || '';

                amountInput.value = amount;
                amountValue.value = amount;
            });

            form.addEventListener('submit', function (event) {
                clearErrors();

                if (!invoiceSelect.value) {
                    event.preventDefault();
                    showError('invoice_id', 'Please select an unpaid invoice.');
                    return;
                }

                if (!amountValue.value) {
                    event.preventDefault();
                    showError('amount', 'Invoice amount is required.');
                    return;
                }

                submitButton.disabled = true;
                submitButton.innerHTML =
                    '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
            });

            // Reopen modal when Laravel redirects back with validation errors.
            @if ($errors->any() && old('invoice_id'))
                modal.show();
            @endif
        });
    </script>
@endif