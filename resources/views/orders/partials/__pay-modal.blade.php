
{{-- Order Payment Modal --}}
<div class="modal fade"
     id="orderPaymentModal"
     tabindex="-1"
     aria-labelledby="orderPaymentModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <form id="orderPaymentForm"
                  method="POST"
                  action=""
                  enctype="multipart/form-data">

                @csrf

                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="orderPaymentModalLabel">
                            Order Payment
                        </h5>
                        <small class="text-muted" id="paymentModalSubtitle">
                            Submit payment information.
                        </small>
                    </div>

                    <button type="button"
                            class="btn-close"
                            data-bs-dismiss="modal"
                            aria-label="Close"></button>
                </div>

                <div class="modal-body">

                    {{-- Order Information --}}
                    <div class="card border-0 mb-3">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6 mb-2">
                                    <small class="text-muted">Order Number</small>
                                    <h6 class="mb-0" id="modalOrderNumber">-</h6>
                                </div>

                                <div class="col-md-6 mb-2 text-md-end">
                                    <small class="text-muted">Payable Amount</small>
                                    <h5 class="mb-0 text-warning">
                                        <span id="modalOrderAmount">0.00</span>
                                    </h5>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Admin-only hidden fields --}}
                    <input type="hidden"
                           name="order_id"
                           id="paymentOrderId">

                    <input type="hidden"
                           name="amount"
                           id="paymentAmount">

                    <input type="hidden"
                           name="status"
                           id="paymentStatus"
                           value="pending">

                    {{-- Payment Gateway --}}
                    <div class="mb-3">
                        <label for="paymentGateway" class="form-label">
                            Payment Gateway <span class="text-danger">*</span>
                        </label>

                        <select name="payment_gateway_id"
                                id="paymentGateway"
                                class="form-select"
                                required>

                            <option value="">Select Payment Gateway</option>

                            @foreach ($paymentGateways as $gateway)
                                <option
                                    value="{{ $gateway->id }}"
                                    data-type="{{ strtolower($gateway->type ?? '') }}"
                                >
                                    {{ $gateway->name }}
                                    @if ($gateway->account_number)
                                        - {{ $gateway->account_number }}
                                    @endif
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Sender Mobile --}}
                    <div class="mb-3" id="senderMobileWrapper">
                        <label for="senderMobile" class="form-label">
                            Sender Mobile / Account Number
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="sender_mobile"
                               id="senderMobile"
                               class="form-control"
                               placeholder="Enter sender mobile or account number"
                               maxlength="100">
                    </div>

                    {{-- Transaction ID --}}
                    <div class="mb-3" id="trxIdWrapper">
                        <label for="trxId" class="form-label">
                            Transaction ID (TrxID)
                            <span class="text-danger">*</span>
                        </label>

                        <input type="text"
                               name="trx_id"
                               id="trxId"
                               class="form-control"
                               placeholder="Enter transaction ID"
                               maxlength="150">
                    </div>

                    {{-- Receipt --}}
                    <div class="mb-3">
                        <label for="paymentReceipt" class="form-label">
                            Payment Receipt
                        </label>

                        <input type="file"
                               name="receipt"
                               id="paymentReceipt"
                               class="form-control"
                               accept=".jpg,.jpeg,.png,.pdf">

                        <small class="text-muted">
                            JPG, JPEG, PNG or PDF. Maximum 5 MB.
                        </small>
                    </div>

                    {{-- Admin Notice --}}
                    <div class="alert alert-info d-none mb-0"
                         id="adminPaymentNotice">
                        <i class="bi bi-info-circle me-1"></i>
                        Admin payment will be approved immediately.
                    </div>

                    {{-- Member Notice --}}
                    <div class="alert alert-warning d-none mb-0"
                         id="memberPaymentNotice">
                        <i class="bi bi-clock-history me-1"></i>
                        Your payment will remain pending until verified by the administrator.
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
                            id="submitOrderPayment">
                        <i class="bi bi-check-circle me-1"></i>
                        Submit Payment
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>



<script>

document.addEventListener('DOMContentLoaded', function () {
    const modal = document.getElementById('orderPaymentModal');

    if (!modal) {
        return;
    }

    const form = document.getElementById('orderPaymentForm');

    const gateway = document.getElementById('paymentGateway');
    const senderMobile = document.getElementById('senderMobile');
    const trxId = document.getElementById('trxId');

    const senderWrapper = document.getElementById('senderMobileWrapper');
    const trxWrapper = document.getElementById('trxIdWrapper');

    const submitButton = document.getElementById('submitOrderPayment');

    const memberNotice = document.getElementById('memberPaymentNotice');
    const adminNotice = document.getElementById('adminPaymentNotice');

    let paymentType = 'member';

    modal.addEventListener('show.bs.modal', function (event) {
        const button = event.relatedTarget;

        if (!button) {
            return;
        }

        const orderId = button.dataset.orderId;
        const orderNumber = button.dataset.orderNumber;
        const amount = button.dataset.orderAmount;

        paymentType = button.dataset.paymentType || 'member';

        form.reset();

        document.getElementById('paymentOrderId').value = orderId;
        document.getElementById('paymentAmount').value = amount;

        document.getElementById('modalOrderNumber').textContent = orderNumber;
        document.getElementById('modalOrderAmount').textContent = amount;

        if (paymentType === 'admin') {
            form.action = @json(route('admin.transactions.store'));

            document.getElementById('paymentStatus').value = 'approved';

            document.getElementById('orderPaymentModalLabel').textContent =
                'Receive Order Payment';

            document.getElementById('paymentModalSubtitle').textContent =
                'Record and approve order payment.';

            submitButton.innerHTML =
                '<i class="bi bi-check-circle me-1"></i> Receive & Approve Payment';

            adminNotice.classList.remove('d-none');
            memberNotice.classList.add('d-none');
        } else {
            const memberPaymentUrl = @json(route('orders.payment', ['order' => '__ORDER__']));

            form.action = memberPaymentUrl.replace('__ORDER__', orderId);

            document.getElementById('paymentStatus').value = 'pending';

            document.getElementById('orderPaymentModalLabel').textContent =
                'Pay for Order';

            document.getElementById('paymentModalSubtitle').textContent =
                'Submit your order payment information.';

            submitButton.innerHTML =
                '<i class="bi bi-credit-card me-1"></i> Submit Payment';

            memberNotice.classList.remove('d-none');
            adminNotice.classList.add('d-none');
        }

        updateGatewayFields();
    });

    gateway.addEventListener('change', updateGatewayFields);

    function updateGatewayFields() {
        const selectedOption = gateway.options[gateway.selectedIndex];

        const gatewayType = selectedOption
            ? selectedOption.dataset.type
            : '';

        const isCash = gatewayType === 'cash';

        senderWrapper.classList.toggle('d-none', isCash);
        trxWrapper.classList.toggle('d-none', isCash);

        senderMobile.required = !isCash;
        trxId.required = !isCash;

        if (isCash) {
            senderMobile.value = '';
            trxId.value = '';
        }
    }
});

</script>