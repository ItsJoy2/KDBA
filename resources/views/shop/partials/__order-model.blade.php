
    {{-- Order Modal --}}
    @if ($canOrder)
        <div class="modal fade"
             id="orderModal"
             tabindex="-1"
             aria-labelledby="orderModalLabel"
             aria-hidden="true">

            <div class="modal-dialog modal-dialog-centered">

                <div class="modal-content">

                    <form action="{{ route('orders.store') }}"
                          method="POST"
                          id="orderForm">

                        @csrf

                        <div class="modal-header">
                            <h5 class="modal-title" id="orderModalLabel">
                                <i class="bi bi-cart-check me-1"></i>
                                Place Order
                            </h5>

                            <button type="button"
                                    class="btn-close"
                                    data-bs-dismiss="modal"
                                    aria-label="Close"></button>
                        </div>

                        <div class="modal-body">

                            {{-- Product ID --}}
                            <input type="hidden" name="items[0][product_id]" id="order_product_id">

                            {{-- Authenticated User --}}
                            <div class="mb-3">
                                <label class="form-label">Ordering As</label>

                                <input type="text"
                                       class="form-control"
                                       value="{{ auth()->user()->full_name }}"
                                       readonly>

                                <div class="form-text">
                                    The order will be created under your account.
                                </div>
                            </div>

                            {{-- Product Name --}}
                            <div class="mb-3">
                                <label class="form-label">Product</label>

                                <input type="text"
                                       class="form-control"
                                       id="order_product_name"
                                       readonly>
                            </div>

                            {{-- Unit Price --}}
                            <div class="mb-3">
                                <label class="form-label">Unit Price</label>

                                <div class="input-group">
                                    <span class="input-group-text">৳</span>

                                    <input type="text"
                                           class="form-control"
                                           id="order_product_price"
                                           readonly>
                                </div>
                            </div>

                            {{-- Quantity --}}
                            <div class="mb-3">
                                <label for="order_quantity" class="form-label">
                                    Quantity <span class="text-danger">*</span>
                                </label>

                                <input type="number" class="form-control" name="items[0][quantity]" id="order_quantity" min="1" value="1"required>

                                <div class="form-text">
                                    Available stock:
                                    <span id="order_available_stock">0</span>
                                </div>
                            </div>

                            {{-- Serial Numbers --}}
                            <div class="mb-3 d-none" id="serialInfoWrapper">
                                <label class="form-label">
                                    Serial Numbers <span class="text-danger">*</span>
                                </label>

                                <div id="serialInputsContainer"></div>

                                <div class="form-text">
                                    Enter one serial number for each item.
                                </div>
                            </div>

                            {{-- Total --}}
                            <div class="alert alert-light border mb-0">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-semibold">Total Amount</span>

                                    <span class="fs-5 fw-bold text-primary">
                                        ৳<span id="order_total_amount">0.00</span>
                                    </span>
                                </div>
                            </div>

                        </div>

                        <div class="modal-footer">
                            <button type="button"
                                    class="btn btn-outline-secondary"
                                    data-bs-dismiss="modal">
                                Cancel
                            </button>

                            <button type="submit"
                                    class="btn btn-primary"
                                    id="confirmOrderButton">
                                <i class="bi bi-check-circle me-1"></i>
                                Confirm Order
                            </button>
                        </div>

                    </form>

                </div>
            </div>
        </div>
    @endif
