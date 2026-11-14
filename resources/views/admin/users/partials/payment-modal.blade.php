
<div class="modal fade" id="adminPaymentModal" tabindex="-1"
     aria-labelledby="adminPaymentModalLabel" aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form
                id="adminPaymentForm"
                action="{{ route('admin.transactions.store') }}"
                method="POST"
                enctype="multipart/form-data"
            >
                @csrf

                <div class="modal-header">
                    <h5 class="modal-title" id="adminPaymentModalLabel">
                        <i class="bi bi-cash-coin me-1"></i>
                        Record Membership Payment
                    </h5>

                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    <div id="paymentModalErrors"
                         class="alert alert-danger d-none">
                        <ul class="mb-0" id="paymentModalErrorList"></ul>
                    </div>

                    <div class="alert alert-info">
                        Recording payment for:
                        <strong id="paymentUserName">—</strong>
                    </div>

                    <input type="hidden" id="paymentUserId">

                    <div class="row g-3">

                        <div class="col-md-6">
                            <label for="paymentInvoiceType" class="form-label">
                                Payment Type
                            </label>

                            <select id="paymentInvoiceType"
                                    class="form-select">
                                <option value="">All payment types</option>
                                <option value="registration">
                                    Initial Membership Fee
                                </option>
                                <option value="yearly_renewal">
                                    Yearly Renewal Fee
                                </option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="paymentInvoiceId" class="form-label">
                                Select Invoice <span class="text-danger">*</span>
                            </label>

                            <select
                                name="invoice_id"
                                id="paymentInvoiceId"
                                class="form-select"
                                required
                            >
                                <option value="">Select invoice</option>
                            </select>

                            <div class="form-text" id="invoiceLoadingMessage"></div>
                        </div>

                        <div class="col-md-6">
                            <label for="paymentGatewayId" class="form-label">
                                Payment Gateway <span class="text-danger">*</span>
                            </label>

                            <select
                                name="payment_gateway_id"
                                id="paymentGatewayId"
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

                        <div class="col-md-6">
                            <label for="paymentAmount" class="form-label">
                                Amount <span class="text-danger">*</span>
                            </label>

                            <input
                                type="number"
                                name="amount"
                                id="paymentAmount"
                                class="form-control"
                                min="0.01"
                                step="0.01"
                                required
                                readonly
                            >

                            <div class="form-text">
                                Amount is taken from the selected invoice.
                            </div>
                        </div>

                        <div class="col-md-6">
                            <label for="paymentSenderMobile" class="form-label">
                                Sender Account / Mobile
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="sender_mobile"
                                id="paymentSenderMobile"
                                class="form-control"
                                maxlength="100"
                                required
                            >
                        </div>

                        <div class="col-md-6">
                            <label for="paymentTrxId" class="form-label">
                                Transaction ID (TrxID)
                                <span class="text-danger">*</span>
                            </label>

                            <input
                                type="text"
                                name="trx_id"
                                id="paymentTrxId"
                                class="form-control"
                                minlength="4"
                                maxlength="150"
                                pattern="[A-Za-z0-9_-]+"
                                required
                            >
                        </div>

                        <div class="col-12">
                            <label for="paymentReceipt" class="form-label">
                                Payment Receipt
                            </label>

                            <input
                                type="file"
                                name="receipt"
                                id="paymentReceipt"
                                class="form-control"
                                accept=".jpg,.jpeg,.png,.pdf"
                            >

                            <div class="form-text">
                                JPG, JPEG, PNG or PDF. Maximum 5 MB.
                            </div>
                        </div>

                        @if(auth()->user()->hasPermission('transactions.verify'))
                            <div class="col-12">
                                <div class="form-check">
                                    <input
                                        type="checkbox"
                                        name="auto_approve"
                                        value="1"
                                        id="paymentAutoApprove"
                                        class="form-check-input"
                                    >

                                    <label class="form-check-label"
                                           for="paymentAutoApprove">
                                        Approve payment immediately
                                    </label>
                                </div>

                                <small class="text-muted">
                                    If unchecked, payment will remain pending
                                    for verification.
                                </small>
                            </div>
                        @endif

                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-bs-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit" class="btn btn-success"
                            id="submitAdminPayment">
                        <i class="bi bi-check-circle me-1"></i>
                        Record Payment
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>


<script>

    document.addEventListener('DOMContentLoaded', function () {
        const modalElement = document.getElementById('adminPaymentModal');

        if (!modalElement) {
            return;
        }

        const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
        const form = document.getElementById('adminPaymentForm');

        const invoiceSelect = document.getElementById('paymentInvoiceId');
        const invoiceTypeSelect = document.getElementById('paymentInvoiceType');
        const amountInput = document.getElementById('paymentAmount');

        const userName = document.getElementById('paymentUserName');
        const userIdInput = document.getElementById('paymentUserId');
        const loadingMessage = document.getElementById('invoiceLoadingMessage');

        const submitButton = document.getElementById('submitAdminPayment');
        const errorContainer = document.getElementById('paymentModalErrors');
        const errorList = document.getElementById('paymentModalErrorList');

        let availableInvoices = [];
        let invoicesUrl = null;

        function showErrors(errors) {
            errorList.innerHTML = '';

            Object.values(errors).flat().forEach(function (message) {
                const li = document.createElement('li');
                li.textContent = message;
                errorList.appendChild(li);
            });

            errorContainer.classList.remove('d-none');
        }

        function clearErrors() {
            errorList.innerHTML = '';
            errorContainer.classList.add('d-none');
        }

        function renderInvoices() {
            const selectedType = invoiceTypeSelect.value;

            invoiceSelect.innerHTML = '';

            const defaultOption = document.createElement('option');
            defaultOption.value = '';
            defaultOption.textContent = 'Select invoice';
            invoiceSelect.appendChild(defaultOption);

            const filteredInvoices = availableInvoices.filter(function (invoice) {
                return !selectedType || invoice.invoice_type === selectedType;
            });

            filteredInvoices.forEach(function (invoice) {
                const option = document.createElement('option');

                option.value = invoice.id;
                option.textContent =
                    `${invoice.invoice_number} - ${invoice.title} - ${Number(invoice.amount).toFixed(2)}`;

                option.dataset.amount = invoice.amount;

                invoiceSelect.appendChild(option);
            });

            if (filteredInvoices.length === 0) {
                loadingMessage.textContent = 'No unpaid invoice available.';
            } else {
                loadingMessage.textContent = '';
            }

            amountInput.value = '';
        }

        async function loadInvoices(url) {
            loadingMessage.textContent = 'Loading invoices...';
            invoiceSelect.disabled = true;

            try {
                const response = await fetch(url, {
                    method: 'GET',
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                });

                if (!response.ok) {
                    throw new Error('Unable to load invoices.');
                }

                const data = await response.json();

                availableInvoices = data.invoices || [];

                renderInvoices();
            } catch (error) {
                availableInvoices = [];
                renderInvoices();

                loadingMessage.textContent = error.message;
            } finally {
                invoiceSelect.disabled = false;
            }
        }

        document.querySelectorAll('.js-open-payment-modal').forEach(function (button) {
            button.addEventListener('click', function () {
                form.reset();
                clearErrors();

                userIdInput.value = button.dataset.userId;
                userName.textContent = button.dataset.userName;

                invoicesUrl = button.dataset.invoicesUrl;

                availableInvoices = [];
                invoiceSelect.innerHTML = '<option value="">Loading...</option>';
                amountInput.value = '';

                loadInvoices(invoicesUrl);

                modal.show();
            });
        });

        invoiceTypeSelect.addEventListener('change', renderInvoices);

        invoiceSelect.addEventListener('change', function () {
            const selectedOption = invoiceSelect.selectedOptions[0];

            amountInput.value = selectedOption?.dataset.amount || '';
        });

        form.addEventListener('submit', function () {
            clearErrors();

            if (! invoiceSelect.value) {
                showErrors({
                    invoice_id: ['Please select an invoice.'],
                });

                return;
            }

            submitButton.disabled = true;
            submitButton.innerHTML =
                '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';
        });

        // Restore button state if browser returns to this page.
        window.addEventListener('pageshow', function () {
            submitButton.disabled = false;
            submitButton.innerHTML =
                '<i class="bi bi-check-circle me-1"></i> Record Payment';
        });
    });

</script>