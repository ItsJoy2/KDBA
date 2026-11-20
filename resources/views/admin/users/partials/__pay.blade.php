
@if(auth()->user()->hasPermission('transactions.create') || auth()->user()->hasPermission('invoices.pay'))
    <div class="modal fade"
         id="adminPaymentModal"
         tabindex="-1"
         aria-labelledby="adminPaymentModalLabel"
         aria-hidden="true">

        <div class="modal-dialog modal-dialog-centered">
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

                        <button type="button"
                                class="btn-close"
                                data-bs-dismiss="modal"
                                aria-label="Close"></button>
                    </div>

                    <div class="modal-body">

                        <div class="alert alert-info">
                            <strong>Member:</strong>
                            <span id="paymentMemberName">—</span>
                        </div>

                        <input type="hidden" id="paymentUserId">

                        {{-- Invoice --}}
                        <div class="mb-3">
                            <label for="paymentInvoice" class="form-label">
                                Select Invoice <span class="text-danger">*</span>
                            </label>

                            <select name="invoice_id"
                                    id="paymentInvoice"
                                    class="form-select"
                                    required>
                                <option value="">Select an invoice</option>
                            </select>

                            <div class="invalid-feedback"
                                 data-error="invoice_id"></div>
                        </div>

                        <div class="row">

                            {{-- Invoice Amount --}}
                            <div class="col-md-6 mb-3">
                                <label for="paymentAmount" class="form-label">
                                    Invoice Amount
                                </label>

                                <input type="text"
                                       id="paymentAmount"
                                       class="form-control"
                                       readonly
                                       placeholder="Select invoice first">

                                <input type="hidden"
                                       name="amount"
                                       id="paymentAmountValue">

                                <div class="invalid-feedback"
                                     data-error="amount"></div>
                            </div>

                            {{-- Payment Gateway --}}
                            <div class="col-md-6 mb-3">
                                <label for="paymentGateway" class="form-label">
                                    Payment Gateway <span class="text-danger">*</span>
                                </label>

                                <select name="payment_gateway_id"
                                        id="paymentGateway"
                                        class="form-select"
                                        required>

                                    <option value="">Select gateway</option>

                                    @foreach($paymentGateways as $gateway)
                                        <option value="{{ $gateway->id }}"
                                                data-type="{{ $gateway->type }}">
                                            {{ $gateway->name }}

                                            @if($gateway->account_number)
                                                ({{ $gateway->account_number }})
                                            @endif
                                        </option>
                                    @endforeach

                                </select>

                                <div class="invalid-feedback"
                                     data-error="payment_gateway_id"></div>
                            </div>

                        </div>

                        <div class="row">

                            {{-- Sender Mobile / Account --}}
                            <div class="col-md-6 mb-3"
                                 id="adminSenderField">

                                <label for="paymentSender" class="form-label">
                                    Sender Mobile / Account
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="sender_mobile"
                                       id="paymentSender"
                                       class="form-control"
                                       maxlength="100"
                                       placeholder="Enter sender mobile or account number"
                                       required>

                                <div class="invalid-feedback"
                                     data-error="sender_mobile"></div>
                            </div>

                            {{-- Transaction ID --}}
                            <div class="col-md-6 mb-3"
                                 id="adminTrxField">

                                <label for="paymentTrxId" class="form-label">
                                    Transaction ID
                                    <span class="text-danger">*</span>
                                </label>

                                <input type="text"
                                       name="trx_id"
                                       id="paymentTrxId"
                                       class="form-control"
                                       minlength="4"
                                       maxlength="150"
                                       pattern="[A-Za-z0-9_-]+"
                                       placeholder="Enter transaction ID"
                                       required>

                                <div class="invalid-feedback"
                                     data-error="trx_id"></div>
                            </div>

                            {{-- Receipt --}}
                            <div class="col-6 mb-3">
                                <label for="paymentReceipt" class="form-label">
                                    Receipt (Optional)
                                </label>

                                <input type="file"
                                    name="receipt"
                                    id="paymentReceipt"
                                    class="form-control"
                                    accept=".jpg,.jpeg,.png,.pdf">

                                <small class="text-muted">
                                    JPG, JPEG, PNG or PDF; maximum 5 MB.
                                </small>

                                <div class="invalid-feedback"
                                    data-error="receipt"></div>
                            </div>

                            {{-- Payment Status --}}
                            <div class=" col-6 mb-3">
                                <label for="adminPaymentStatus" class="form-label">
                                    Payment Status
                                    <span class="text-danger">*</span>
                                </label>

                                <select name="status"
                                        id="adminPaymentStatus"
                                        class="form-select"
                                        required>

                                    @if(auth()->user()->hasPermission('transactions.verify'))
                                        <option value="approved" selected>
                                            Approved
                                        </option>
                                    @endif

                                    <option value="pending"
                                        @if(!auth()->user()->hasPermission('transactions.verify')) selected @endif>
                                        Pending
                                    </option>

                                </select>

                                <small class="text-muted">
                                    Approved payments are recorded immediately.
                                    Pending payments require verification.
                                </small>
                            </div>
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
                                id="submitPaymentBtn"
                                @if($paymentGateways->isEmpty()) disabled @endif>

                            <i class="bi bi-check-circle me-1"></i>
                            Submit Payment
                        </button>

                    </div>

                </form>

            </div>
        </div>
    </div>
@endif


<script>

document.addEventListener('DOMContentLoaded', function () {

    const form = document.getElementById('adminPaymentForm');

    if (!form) {
        return;
    }

    const gatewayInput = document.getElementById('paymentGateway');

    const senderField = document.getElementById('adminSenderField');
    const trxField = document.getElementById('adminTrxField');

    const senderInput = document.getElementById('paymentSender');
    const trxInput = document.getElementById('paymentTrxId');

    const submitButton = document.getElementById('submitPaymentBtn');

    const initialSubmitDisabled = {{ $paymentGateways->isEmpty() ? 'true' : 'false' }};

    /**
     * Show/hide payment fields based on gateway.
     */
    function toggleAdminCashFields() {

        const selectedOption =
            gatewayInput.options[gatewayInput.selectedIndex];

        const isCash = selectedOption?.dataset.type === 'cash';

        senderField.classList.toggle('d-none', isCash);
        trxField.classList.toggle('d-none', isCash);

        senderInput.required = !isCash;
        trxInput.required = !isCash;

        if (isCash) {
            senderInput.value = '';
            trxInput.value = '';
        }
    }

    // Gateway change event.
    gatewayInput.addEventListener('change', toggleAdminCashFields);

    // Initial state.
    toggleAdminCashFields();

    // Reset form when modal closes.
    const modalElement = document.getElementById('adminPaymentModal');

    modalElement.addEventListener('hidden.bs.modal', function () {

        form.reset();

        toggleAdminCashFields();

        submitButton.disabled = initialSubmitDisabled;

        submitButton.innerHTML =
            '<i class="bi bi-check-circle me-1"></i> Submit Payment';
    });

    // Prevent duplicate submission.
    form.addEventListener('submit', function (event) {

        if (!form.reportValidity()) {
            event.preventDefault();
            return;
        }

        if (submitButton.disabled) {
            event.preventDefault();
            return;
        }

        submitButton.disabled = true;

        submitButton.innerHTML =
            '<span class="spinner-border spinner-border-sm me-1"></span> Submitting...';
    });

});

</script>