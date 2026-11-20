<div class="modal fade"
     id="invoiceViewModal{{ $invoice->id }}"
     tabindex="-1"
     aria-labelledby="invoiceViewModalLabel{{ $invoice->id }}"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">

            <div class="modal-header py-2">
                <div>
                    <h6 class="modal-title mb-0"
                        id="invoiceViewModalLabel{{ $invoice->id }}">
                        Invoice Details
                    </h6>
                    <small class="text-muted">
                        {{ $invoice->invoice_number }}
                    </small>
                </div>

                <button type="button" class="btn-close"
                        data-bs-dismiss="modal"></button>
            </div>

            <div class="modal-body p-3">

                {{-- Information --}}
                <div class="row g-2 mb-3">

                    <div class="col-md-6">
                        <div class="border rounded p-2 h-100">
                            <h6 class="text-primary small mb-2">
                                <i class="bi bi-person"></i> Member
                            </h6>

                            <div class="small mb-1">
                                <strong>Name:</strong>
                                {{ $invoice->user->full_name ?? 'N/A' }}
                            </div>

                            <div class="small mb-1">
                                <strong>Membership:</strong>
                                {{ $invoice->user->membership_number ?? 'N/A' }}
                            </div>

                            <div class="small mb-1">
                                <strong>Mobile:</strong>
                                {{ $invoice->user->mobile ?? 'N/A' }}
                            </div>

                            <div class="small">
                                <strong>Email:</strong>
                                {{ $invoice->user->email ?? 'N/A' }}
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <div class="border rounded p-2 h-100">
                            <h6 class="text-primary small mb-2">
                                <i class="bi bi-receipt"></i> Invoice
                            </h6>

                            <div class="small mb-1">
                                <strong>Type:</strong>
                                {{ str($invoice->invoice_type)->replace('_', ' ')->title() }}
                            </div>

                            <div class="small mb-1">
                                <strong>Cycle:</strong>
                                {{ $invoice->cycle_key ?? '—' }}
                            </div>

                            <div class="small mb-1">
                                <strong>Issue Date:</strong>
                                {{ $invoice->created_at?->format('d M Y') ?? '—' }}
                            </div>

                            <div class="small mb-1">
                                <strong>Due Date:</strong>
                                {{ $invoice->due_date?->format('d M Y') ?? '—' }}
                            </div>

                            <div class="small">
                                <strong>Status:</strong>
                                <span class="badge bg-secondary">
                                    {{ str($invoice->status)->replace('_', ' ')->title() }}
                                </span>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Invoice Items --}}
                <h6 class="small mb-2">
                    <i class="bi bi-list-ul"></i> Invoice Items
                </h6>

                <div class="table-responsive">
                    <table class="table table-sm table-bordered align-middle mb-2">
                        <thead>
                            <tr>
                                <th width="35">#</th>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Period</th>
                                <th class="text-end">Unit Amount</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>

                                    <td class="small">
                                        <strong>{{ $item->title ?? '—' }}</strong>
                                    </td>

                                    <td class="small">
                                        {{ $item->description ?? '—' }}
                                    </td>

                                    <td class="small">
                                        @if($item->period_start || $item->period_end)
                                            {{ $item->period_start?->format('d M Y') ?? '—' }}
                                            <br>
                                            <span class="text-muted">to</span>
                                            <br>
                                            {{ $item->period_end?->format('d M Y') ?? '—' }}
                                        @else
                                            —
                                        @endif
                                    </td>

                                    <td class="text-end small">
                                        {{ number_format((float) $item->unit_amount, 2) }}
                                    </td>

                                    <td class="text-end small">
                                        <strong>
                                            {{ number_format((float) $item->amount, 2) }}
                                        </strong>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6"
                                        class="text-center text-muted small py-3">
                                        No invoice items found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>

                        <tfoot>
                            <tr>
                                <th colspan="5" class="text-end">
                                    Invoice Total
                                </th>
                                <th class="text-end">
                                    {{ number_format((float) $invoice->amount, 2) }}
                                </th>
                            </tr>
                        </tfoot>
                    </table>
                </div>

                @if($invoice->notes)
                    <div class="border rounded p-2 small">
                        <strong>Note:</strong>
                        {{ $invoice->notes }}
                    </div>
                @endif

            </div>

            <div class="modal-footer py-2">
                <button type="button"
                        class="btn btn-sm btn-secondary"
                        data-bs-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>