
{{-- Payment Modal --}}
<div class="modal fade" id="memberPaymentModal" tabindex="-1"
     aria-labelledby="memberPaymentModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">

            <form id="memberPaymentForm"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="memberPaymentModalLabel">
                        <i class="bi bi-credit-card me-1"></i>
                        Submit Invoice Payment
                    </h5>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    {{-- Validation Errors --}}
                    <div id="memberPaymentErrors"
                         class="alert alert-danger d-none">
                        <ul class="mb-0" id="memberPaymentErrorList"></ul>
                    </div>

                    {{-- Invoice Information --}}
                    <div class="alert alert-info">
                        <div class="mb-2">
                            Invoice:
                            <strong id="memberInvoiceNumber">—</strong>
                        </div>

                        <div>
                            Invoice Amount:
                            <strong id="memberInvoiceAmount">৳0.00</strong>
                        </div>
                    </div>

                    {{-- Payment Gateway --}}
                    <div class="mb-3">
                        <label for="memberPaymentGateway" class="form-label">
                            Payment Gateway
                            <span class="text-danger">*</span>
                        </label>

                        <select
                            name="payment_gateway_id"
                            id="memberPaymentGateway"
                            class="form-select"
                            required
                        >
                            <option value="">Select gateway</option>

                            @foreach($paymentGateways as $gateway)
                                <option value="{{ $gateway->id }}">
                                    {{ $gateway->name }}
                                    @if($gateway->account_number)
                                        ({{ $gateway->account_number }})
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Payment Amount --}}
                    <div class="mb-3">
                        <label for="memberPaymentAmount" class="form-label">
                            Payment Amount
                        </label>

                        <div class="input-group">
                            <span class="input-group-text">৳</span>

                            <input
                                type="number"
                                id="memberPaymentAmount"
                                name="amount"
                                class="form-control"
                                step="0.01"
                                min="0.01"
                                readonly
                                required
                            >
                        </div>

                        <small class="text-muted">
                            Invoice amount is automatically filled.
                        </small>
                    </div>

                    {{-- Sender Mobile --}}
                    <div class="mb-3">
                        <label for="memberSenderMobile" class="form-label">
                            Sender Account / Mobile
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="sender_mobile"
                            id="memberSenderMobile"
                            class="form-control"
                            maxlength="100"
                            autocomplete="off"
                            required
                        >
                    </div>

                    {{-- Transaction ID --}}
                    <div class="mb-3">
                        <label for="memberTrxId" class="form-label">
                            Transaction ID (TrxID)
                            <span class="text-danger">*</span>
                        </label>

                        <input
                            type="text"
                            name="trx_id"
                            id="memberTrxId"
                            class="form-control"
                            minlength="4"
                            maxlength="150"
                            pattern="[A-Za-z0-9_-]+"
                            autocomplete="off"
                            required
                        >

                        <small class="text-muted">
                            Only letters, numbers, underscore and hyphen.
                        </small>
                    </div>

                    {{-- Receipt --}}
                    <div class="mb-3">
                        <label for="memberReceipt" class="form-label">
                            Payment Receipt
                        </label>

                        <input
                            type="file"
                            name="receipt"
                            id="memberReceipt"
                            class="form-control"
                            accept=".jpg,.jpeg,.png,.pdf"
                        >

                        <small class="text-muted">
                            JPG, JPEG, PNG or PDF. Maximum 5 MB.
                        </small>
                    </div>

                    <div class="alert alert-warning mb-0">
                        <i class="bi bi-info-circle me-1"></i>
                        Your payment will remain pending until an authorized
                        administrator verifies it.
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button"
                            class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-success"
                            id="memberPaymentSubmit">
                        <i class="bi bi-send me-1"></i>
                        Submit Payment
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>