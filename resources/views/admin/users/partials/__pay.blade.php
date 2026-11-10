@if(auth()->user()->hasPermission('transactions.create'))
    <div class="modal fade" id="adminPaymentModal" tabindex="-1"
         aria-labelledby="adminPaymentModalLabel" aria-hidden="true">

        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content">

                <form
                    action="{{ route('admin.transactions.store') }}"
                    method="POST"
                    enctype="multipart/form-data"
                    id="adminPaymentForm"
                >
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title" id="adminPaymentModalLabel">
                            <i class="bi bi-cash-coin me-2"></i>
                            Record Membership Payment
                        </h5>

                        <button type="button" class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="alert alert-info">
                            <strong>Member:</strong>
                            <span id="paymentMemberName">—</span>
                        </div>

                        <input type="hidden" id="paymentUserId">

                        <div class="mb-3">
                            <label for="paymentInvoice" class="form-label">
                                Select Invoice <span class="text-danger">*</span>
                            </label>

                            <select name="invoice_id" id="paymentInvoice"
                                    class="form-select" required>
                                <option value="">Select an invoice</option>
                            </select>

                            <div class="invalid-feedback" data-error="invoice_id"></div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paymentAmount" class="form-label">
                                    Invoice Amount
                                </label>

                                <input type="text" id="paymentAmount"
                                       class="form-control" readonly
                                       placeholder="Select invoice first">

                                <input type="hidden" name="amount"
                                       id="paymentAmountValue">

                                <div class="invalid-feedback" data-error="amount"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="paymentGateway" class="form-label">
                                    Payment Gateway <span class="text-danger">*</span>
                                </label>

                                <select name="payment_gateway_id" id="paymentGateway"
                                        class="form-select" required>
                                    <option value="">Select gateway</option>

                                    @foreach($paymentGateways as $gateway)
                                        <option value="{{ $gateway->id }}">
                                            {{ $gateway->name }}
                                            ({{ $gateway->account_number }})
                                        </option>
                                    @endforeach
                                </select>

                                <div class="invalid-feedback"
                                     data-error="payment_gateway_id"></div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="paymentSender" class="form-label">
                                    Sender Mobile / Account
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="sender_mobile"
                                       id="paymentSender" class="form-control"
                                       maxlength="30" required>

                                <div class="invalid-feedback"
                                     data-error="sender_mobile"></div>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="paymentTrxId" class="form-label">
                                    Transaction ID
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text" name="trx_id"
                                       id="paymentTrxId" class="form-control"
                                       minlength="4" maxlength="100"
                                       pattern="[A-Za-z0-9_-]+"
                                       required>

                                <div class="invalid-feedback" data-error="trx_id"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="paymentReceipt" class="form-label">
                                Receipt (Optional)
                            </label>

                            <input type="file" name="receipt" id="paymentReceipt"
                                   class="form-control"
                                   accept=".jpg,.jpeg,.png,.pdf">

                            <small class="text-muted">
                                JPG, JPEG, PNG or PDF; maximum 5 MB.
                            </small>

                            <div class="invalid-feedback" data-error="receipt"></div>
                        </div>

                        @if(auth()->user()->hasPermission('transactions.verify'))
                            <div class="form-check border rounded p-3">
                                <input type="checkbox" name="auto_approve"
                                       value="1" class="form-check-input ms-0 me-2"
                                       id="paymentAutoApprove">

                                <label class="form-check-label"
                                       for="paymentAutoApprove">
                                    <strong>Approve payment immediately</strong>
                                    <div class="small text-muted">
                                        Use only after independently confirming the payment.
                                    </div>
                                </label>
                            </div>
                        @endif

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary"
                                data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-success"
                                id="submitPaymentBtn">
                            <i class="bi bi-check-circle me-1"></i>
                            Submit Payment
                        </button>
                    </div>

                </form>

            </div>
        </div>
    </div>
@endif