{{-- TRANSACTION DETAILS MODALS --}}

@foreach($transactions as $transaction)

    <div class="modal fade"
         id="transactionModal-{{ $transaction->id }}"
         tabindex="-1"
         aria-labelledby="transactionModalLabel-{{ $transaction->id }}"
         aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">

            <div class="modal-content border-0 shadow">

                {{-- Header --}}
                <div class="modal-header bg-light">

                    <div>
                        <h5 class="modal-title fw-bold"
                            id="transactionModalLabel-{{ $transaction->id }}">

                            <i class="bi bi-receipt me-2 text-primary"></i>
                            Transaction Details

                        </h5>

                        <small class="text-muted">
                            Reference: {{ $transaction->trx_id ?? '—' }}
                        </small>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close">
                    </button>

                </div>

                {{-- Body --}}
                <div class="modal-body">

                    {{-- Amount Summary --}}
                    <div class="card border-0 mb-4">

                        <div class="card-body">

                            <div class="row align-items-center g-3">

                                <div class="col-md-6">

                                    <small class="text-muted">
                                        Transaction Amount
                                    </small>

                                    <h4 class="fw-bold text-primary mb-0">
                                        ৳{{ number_format((float) $transaction->amount, 2) }}
                                    </h3>

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Transaction Type
                                    </small>

                                    @if($transaction->category?->type === 'income')

                                        <span class="badge bg-success fs-6">
                                            Income
                                        </span>

                                    @elseif($transaction->category?->type === 'expense')

                                        <span class="badge bg-danger fs-6">
                                            Expense
                                        </span>

                                    @else

                                        <span class="badge bg-secondary">
                                            N/A
                                        </span>

                                    @endif

                                </div>

                                <div class="col-md-3">

                                    <small class="text-muted d-block">
                                        Status
                                    </small>

                                    @php
                                        $statusClass = match($transaction->status) {
                                            'approved' => 'success',
                                            'pending' => 'warning',
                                            'rejected' => 'danger',
                                            default => 'secondary',
                                        };
                                    @endphp

                                    <span class="badge bg-{{ $statusClass }} fs-6">
                                        {{ ucfirst($transaction->status) }}
                                    </span>

                                </div>

                            </div>

                        </div>

                    </div>


                    {{-- Transaction Information --}}
                    <div class="mb-4">

                        <h6 class="fw-bold border-bottom pb-2 mb-3">
                            <i class="bi bi-info-circle me-1 text-primary"></i>
                            Transaction Information
                        </h6>

                        <div class="row g-3">

                            <div class="col-md-4">
                                <small class="text-muted">Transaction ID</small>
                                <div class="fw-semibold">
                                    {{ $transaction->trx_id ?? '—' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Transaction Date</small>
                                <div class="fw-semibold">
                                    {{ $transaction->created_at?->format('d M Y, h:i A') ?? '—' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Category</small>
                                <div class="fw-semibold">
                                    {{ $transaction->category?->name ?? 'Uncategorized' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Payment Gateway</small>
                                <div class="fw-semibold">
                                    {{ $transaction->paymentGateway?->name ?? 'N/A' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Recorded By</small>
                                <div class="fw-semibold">
                                    {{ $transaction->recordedBy?->full_name ?? 'System' }}
                                </div>
                            </div>

                            <div class="col-md-4">
                                <small class="text-muted">Recorded By Role</small>
                                <div class="fw-semibold">
                                    {{ $transaction->recordedBy?->roles?->pluck('name')->join(', ') ?: 'System' }}
                                </div>
                            </div>

                            <div class="col-12">
                                <small class="text-muted">Description</small>
                                <div class="fw-semibold">
                                    {{ $transaction->description ?? '—' }}
                                </div>
                            </div>

                        </div>

                    </div>


                    {{-- User Information --}}
                    @if($transaction->user)

                        <div class="mb-4">

                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                <i class="bi bi-person me-1 text-primary"></i>
                                User Information
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <small class="text-muted">Full Name</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->user->full_name ?? '—' }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <small class="text-muted">Membership Number</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->user->membership_number ?? '—' }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <small class="text-muted">Email</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->user->email ?? '—' }}
                                    </div>
                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- Invoice Information --}}
                    @if($transaction->invoice)

                        <div class="mb-4">

                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                <i class="bi bi-file-earmark-text me-1 text-primary"></i>
                                Invoice Information
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-4">
                                    <small class="text-muted">Invoice Number</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->invoice->invoice_number ?? '—' }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <small class="text-muted">Invoice Type</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->invoice->invoice_type ?? '—' }}
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <small class="text-muted">Invoice Title</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->invoice->title ?? '—' }}
                                    </div>
                                </div>

                            </div>

                        </div>

                    @endif


                    {{-- Order Information --}}
                    @if($transaction->order)

                        <div class="mb-4">

                            <h6 class="fw-bold border-bottom pb-2 mb-3">
                                <i class="bi bi-cart me-1 text-primary"></i>
                                Order Information
                            </h6>

                            <div class="row g-3">

                                <div class="col-md-6">
                                    <small class="text-muted">Order Number</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->order->order_number ?? '—' }}
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <small class="text-muted">Customer</small>
                                    <div class="fw-semibold">
                                        {{ $transaction->order->user?->full_name ?? '—' }}
                                    </div>
                                </div>

                            </div>

                        </div>

                    @endif

                </div>


                {{-- Footer --}}
                <div class="modal-footer bg-light">

                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">

                        <i class="bi bi-x-circle me-1"></i>
                        Close

                    </button>

                </div>

            </div>

        </div>

    </div>

@endforeach