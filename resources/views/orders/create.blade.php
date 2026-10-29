@extends('layouts.app')

@section('title', 'Create Order')

@section('content')
<div class="app-content-header">
    <div class="container-fluid">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
            <div>
                <h3 class="mb-1 fw-bold">Create Order</h3>
                <p class="text-muted mb-0">
                    Search products and select multiple items for an order.
                </p>
            </div>

            <a href="{{ route('shop.index') }}" class="btn btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Products
            </a>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">
        <div class="row justify-content-center">
            <div class="col-12 col-xxl-10">
                <div class="mx-auto" style="max-width: 700px;">
                    @include('components.global-feedback')

                    <form method="POST" action="{{ route('orders.store') }}" id="orderForm">
                        @csrf

                        {{-- Order Recipient --}}
                        @if ($canOrderForOthers)
                            <div class="card border-0 shadow-sm mb-4">
                                <div class="card-header py-3">
                                    <h5 class="mb-0 fw-semibold">
                                        <i class="bi bi-person-search me-2 text-primary"></i>
                                        Order Recipient
                                    </h5>
                                </div>

                                <div class="card-body">
                                    <label for="memberSearch" class="form-label fw-semibold">
                                        Search Member
                                    </label>

                                    <input
                                        type="hidden"
                                        name="user_id"
                                        id="selectedUserId"
                                        value="{{ old('user_id') }}"
                                    >

                                    <div class="position-relative">
                                        <input
                                            type="search"
                                            id="memberSearch"
                                            class="form-control form-control-lg"
                                            placeholder="Search by name, mobile, email or membership number..."
                                            autocomplete="off"
                                        >

                                        <div
                                            id="memberSuggestions"
                                            class="list-group position-absolute w-100 shadow"
                                            style="z-index:1050; display:none; max-height:280px; overflow-y:auto;"
                                        ></div>
                                    </div>

                                    <div id="selectedMember" class="mt-3" style="display:none;"></div>

                                    <div class="form-text mt-2">
                                        Search at least 2 characters and select a member.
                                        Leave empty to use the default Super Admin recipient.
                                    </div>

                                    @error('user_id')
                                        <div class="text-danger small mt-2">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        @endif

                        {{-- Product Search --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header py-3">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi bi-search me-2 text-primary"></i>
                                    Search Products
                                </h5>
                            </div>

                            <div class="card-body">
                                <label for="productSearch" class="form-label fw-semibold">
                                    Product Name
                                </label>

                                <div class="position-relative">
                                    <input
                                        type="search"
                                        id="productSearch"
                                        class="form-control form-control-lg"
                                        placeholder="Type product name to search..."
                                        autocomplete="off"
                                    >

                                    <div
                                        id="productSuggestions"
                                        class="list-group position-absolute w-100 shadow"
                                        style="display:none; z-index:1050; max-height:320px; overflow-y:auto;"
                                    ></div>
                                </div>

                                <div class="form-text">
                                    Select multiple products from the suggestions. You can change
                                    quantities or remove products below.
                                </div>
                            </div>
                        </div>

                        {{-- Selected Products --}}
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                                <h5 class="mb-0 fw-semibold">
                                    <i class="bi bi-cart3 me-2 text-primary"></i>
                                    Selected Products
                                </h5>

                                <span class="badge text-bg-primary" id="selectedProductCount">
                                    0 products
                                </span>
                            </div>

                            <div class="card-body">
                                <div id="selectedProductsEmpty" class="text-center py-4">
                                    <i class="bi bi-cart-plus display-5 text-muted"></i>

                                    <p class="text-muted mt-2 mb-0">
                                        Search and select products to start an order.
                                    </p>
                                </div>

                                <div class="table-responsive">
                                    <table
                                        class="table table-hover align-middle"
                                        id="selectedProductsTable"
                                        style="display:none;"
                                    >
                                        <thead>
                                            <tr>
                                                <th>Product</th>
                                                <th class="text-end">Price</th>
                                                <th>Stock</th>
                                                <th >Quantity</th>
                                                <th >Serial Numbers</th>
                                                <th class="text-end">Subtotal</th>
                                                <th class="text-center">Action</th>
                                            </tr>
                                        </thead>

                                        <tbody id="selectedProductsBody"></tbody>

                                        <tfoot>
                                            <tr>
                                                <th colspan="5" class="text-end">
                                                    Order Total
                                                </th>

                                                <th class="text-end" id="orderTotal">
                                                    0.00
                                                </th>

                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="d-flex flex-wrap justify-content-end gap-2 mb-4">
                            <a href="{{ route('shop.index') }}" class="btn btn-light border px-4">
                                Cancel
                            </a>

                            <button
                                type="submit"
                                class="btn btn-primary px-4"
                                id="submitOrder"
                            >
                                <i class="bi bi-check2-circle me-1"></i>
                                Place Order
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('orderForm');

    // Validation failure er por restore korar data
    const oldItems = @json(old('items', []));
    const itemErrors = @json($itemErrors ?? []);

    // Common helpers

    function formatMoney(value) {
        return Number(value || 0).toLocaleString(undefined, {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
        });
    }

    function createElement(tag, className, text) {
        const element = document.createElement(tag);

        if (className) {
            element.className = className;
        }

        if (text !== undefined && text !== null) {
            element.textContent = text;
        }

        return element;
    }

    async function fetchSuggestions(url, signal) {
        const response = await fetch(url, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal
        });

        if (!response.ok) {
            throw new Error('Request failed.');
        }

        return response.json();
    }

    function addFeedback(parent, messages) {
        messages.forEach(function (message) {
            parent.appendChild(
                createElement('div', 'invalid-feedback d-block', message)
            );
        });
    }

    function clearFieldError(input, container, key) {
        input.classList.remove('is-invalid');

        container.querySelectorAll('.invalid-feedback').forEach(function (el) {
            el.remove();
        });

        delete itemErrors[key];
    }

    // Product search and multiple product selection

    const productSearch = document.getElementById('productSearch');
    const productSuggestions = document.getElementById('productSuggestions');
    const selectedProductsBody = document.getElementById('selectedProductsBody');
    const selectedProductsTable = document.getElementById('selectedProductsTable');
    const selectedProductsEmpty = document.getElementById('selectedProductsEmpty');
    const selectedProductCount = document.getElementById('selectedProductCount');
    const totalEl = document.getElementById('orderTotal');
    const submitOrder = document.getElementById('submitOrder');

    const selectedProducts = new Map();

    let productTimer = null;
    let productController = null;
    let productSequence = 0;

    function normalizeProduct(product) {
        return {
            id: Number(product.id),
            name: String(product.name || ''),
            selling_price: Number(product.selling_price || 0),
            stock_quantity: Math.max(
                0,
                parseInt(product.stock_quantity || 0, 10) || 0
            ),
            track_serials: [
                true, 1, '1'
            ].includes(product.track_serials),
            serial_start: product.serial_start ?? '',
            serial_end: product.serial_end ?? '',
            current_serial: product.current_serial ?? ''
        };
    }

    function hideProductSuggestions() {
        productSuggestions.replaceChildren();
        productSuggestions.style.display = 'none';
    }

    function showProductMessage(message) {
        productSuggestions.replaceChildren();

        productSuggestions.appendChild(
            createElement('div', 'list-group-item text-muted', message)
        );

        productSuggestions.style.display = 'block';
    }

    function updateProductSummary() {
        const count = selectedProducts.size;

        selectedProductCount.textContent =
            `${count} ${count === 1 ? 'product' : 'products'}`;

        selectedProductsEmpty.style.display = count ? 'none' : 'block';
        selectedProductsTable.style.display = count ? 'table' : 'none';

        submitOrder.disabled = count === 0;
    }

    function updateTotals() {
        let total = 0;

        selectedProducts.forEach(function (product) {
            total += product.selling_price * product.quantity;
        });

        totalEl.textContent = formatMoney(total);
        updateProductSummary();
    }

    function addProduct(productData) {
        const product = normalizeProduct(productData);
        const id = String(product.id);

        if (!product.id || product.stock_quantity < 1) {
            alert('This product is out of stock.');
            return;
        }

        if (selectedProducts.has(id)) {
            const existing = selectedProducts.get(id);

            if (existing.quantity >= existing.stock_quantity) {
                alert(
                    `Maximum available quantity for ${existing.name} is ` +
                    `${existing.stock_quantity}.`
                );
                return;
            }

            existing.quantity++;
        } else {
            selectedProducts.set(id, {
                ...product,
                quantity: 1,
                serials: []
            });
        }

        renderSelectedProducts();
    }

    function createSerialInput(productId, index, value = '') {
        const key = `items.${productId}.serials.${index}`;
        const messages = itemErrors[key] || [];

        const wrapper = createElement(
            'div',
            'input-group input-group-sm mb-2'
        );

        const label = createElement(
            'span',
            'input-group-text',
            `Serial ${index + 1}`
        );

        const input = document.createElement('input');

        input.type = 'number';
        input.step = '1';
        input.min = '0';
        input.required = true;
        input.className = 'form-control serial-number-input';
        input.placeholder = `Enter serial ${index + 1}`;
        input.name = `items[${productId}][serials][${index}]`;
        input.value = value;

        if (messages.length) {
            input.classList.add('is-invalid');
        }

        input.addEventListener('input', function () {
            const product = selectedProducts.get(String(productId));

            if (product) {
                product.serials[index] = input.value;
            }

            clearFieldError(input, wrapper, key);
        });

        wrapper.append(label, input);
        addFeedback(wrapper, messages);

        return wrapper;
    }

    function renderSelectedProducts() {
        selectedProductsBody.replaceChildren();

        selectedProducts.forEach(function (product, id) {
            const row = document.createElement('tr');

            row.className = 'product-row';
            row.dataset.productId = id;
            row.dataset.price = String(product.selling_price);
            row.dataset.stock = String(product.stock_quantity);
            row.dataset.tracksSerials = product.track_serials ? '1' : '0';

            // Product name
            const nameCell = document.createElement('td');

            nameCell.append(
                createElement('div', 'fw-semibold', product.name),
                createElement(
                    'small',
                    'text-muted d-block',
                    product.track_serials ? 'Serial-tracked product' : 'No serials required'
                )
            );

            // Hidden product_id (server validation er jonno)
            const productIdInput = document.createElement('input');

            productIdInput.type = 'hidden';
            productIdInput.name = `items[${id}][product_id]`;
            productIdInput.value = String(product.id);

            nameCell.appendChild(productIdInput);

            // Product-level errors (items.{id} / items.{id}.serials)
            [`items.${id}`, `items.${id}.serials`].forEach(function (key) {
                (itemErrors[key] || []).forEach(function (message) {
                    nameCell.appendChild(
                        createElement('div', 'text-danger small', message)
                    );
                });
            });

            // Unit price
            const priceCell = createElement(
                'td',
                'text-end text-nowrap',
                formatMoney(product.selling_price)
            );

            // Stock
            const stockCell = createElement(
                'td',
                '',
                String(product.stock_quantity)
            );

            // Quantity
            const quantityCell = document.createElement('td');
            const quantityInput = document.createElement('input');

            quantityInput.type = 'number';
            quantityInput.name = `items[${id}][quantity]`;
            quantityInput.className = 'form-control form-control-sm quantity-input';
            quantityInput.min = '0';
            quantityInput.max = String(product.stock_quantity);
            quantityInput.step = '1';
            quantityInput.value = String(product.quantity);
            quantityInput.required = true;

            quantityInput.addEventListener('change', function () {
                let quantity = Number.parseInt(quantityInput.value || '0', 10);

                if (!Number.isInteger(quantity) || quantity < 0) {
                    quantity = 0;
                }

                if (quantity > product.stock_quantity) {
                    alert(
                        `Maximum available quantity for ${product.name} is ` +
                        `${product.stock_quantity}.`
                    );

                    quantity = product.stock_quantity;
                }

                product.quantity = quantity;
                product.serials = product.serials.slice(0, quantity);

                renderSelectedProducts();
            });

            const quantityKey = `items.${id}.quantity`;
            const quantityMessages = itemErrors[quantityKey] || [];

            if (quantityMessages.length) {
                quantityInput.classList.add('is-invalid');
            }

            quantityInput.addEventListener('input', function () {
                clearFieldError(quantityInput, quantityCell, quantityKey);
            });

            quantityCell.appendChild(quantityInput);
            addFeedback(quantityCell, quantityMessages);

            // Serial numbers
            const serialCell = document.createElement('td');
            serialCell.style.minWidth = '150px';

            if (product.track_serials) {
                const serialCount = Math.min(
                    product.quantity,
                    product.stock_quantity
                );

                for (let i = 0; i < serialCount; i++) {
                    serialCell.appendChild(
                        createSerialInput(id, i, product.serials[i] ?? '')
                    );
                }

                if (product.quantity > product.stock_quantity) {
                    serialCell.appendChild(
                        createElement(
                            'div',
                            'text-danger small',
                            'Quantity exceeds available stock.'
                        )
                    );
                }
            } else {
                serialCell.appendChild(
                    createElement('span', 'text-muted small', 'Not required')
                );
            }

            // Subtotal
            const subtotalCell = createElement(
                'td',
                'text-end text-nowrap subtotal',
                formatMoney(product.selling_price * product.quantity)
            );

            // Remove
            const actionCell = createElement('td', 'text-center');

            const removeButton = createElement(
                'button',
                'btn btn-sm btn-outline-danger',
                ''
            );

            removeButton.type = 'button';
            removeButton.innerHTML = '<i class="bi bi-trash"></i>';

            removeButton.addEventListener('click', function () {
                selectedProducts.delete(id);
                renderSelectedProducts();
            });

            actionCell.appendChild(removeButton);

            row.append(
                nameCell,
                priceCell,
                stockCell,
                quantityCell,
                serialCell,
                subtotalCell,
                actionCell
            );

            selectedProductsBody.appendChild(row);
        });

        updateTotals();
    }

    function renderProductSuggestions(products) {
        productSuggestions.replaceChildren();

        if (!products.length) {
            showProductMessage('No matching products found.');
            return;
        }

        products.forEach(function (productData) {
            const product = normalizeProduct(productData);
            const button = document.createElement('button');

            button.type = 'button';
            button.className =
                'list-group-item list-group-item-action d-flex ' +
                'justify-content-between align-items-center gap-3';

            const details = document.createElement('div');

            details.append(
                createElement('div', 'fw-semibold', product.name),
                createElement(
                    'small',
                    'text-muted d-block',
                    `Stock: ${product.stock_quantity}` +
                    (product.track_serials ? ' · Serial tracked' : '')
                )
            );

            const price = createElement(
                'span',
                'fw-semibold text-nowrap',
                formatMoney(product.selling_price)
            );

            button.append(details, price);

            button.addEventListener('click', function () {
                addProduct(product);
                hideProductSuggestions();

                productSearch.value = '';
                productSearch.focus();
            });

            productSuggestions.appendChild(button);
        });

        productSuggestions.style.display = 'block';
    }

    if (productSearch) {
        productSearch.addEventListener('input', function () {
            clearTimeout(productTimer);

            const term = productSearch.value.trim();
            const sequence = ++productSequence;

            if (productController) {
                productController.abort();
            }

            if (term.length < 2) {
                hideProductSuggestions();
                return;
            }

            productTimer = setTimeout(async function () {
                productController = new AbortController();

                try {
                    const url = new URL(
                        @json(route('orders.search')),
                        window.location.origin
                    );

                    url.searchParams.set('type', 'product');
                    url.searchParams.set('q', term);

                    const products = await fetchSuggestions(
                        url,
                        productController.signal
                    );

                    if (sequence !== productSequence) {
                        return;
                    }

                    renderProductSuggestions(products);
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        showProductMessage('Unable to search products. Please try again.');
                    }
                }
            }, 250);
        });

        productSearch.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                hideProductSuggestions();
            }
        });

        document.addEventListener('click', function (event) {
            if (
                !productSuggestions.contains(event.target) &&
                event.target !== productSearch
            ) {
                hideProductSuggestions();
            }
        });
    }

    // Member search

    const memberSearch = document.getElementById('memberSearch');
    const memberSuggestions = document.getElementById('memberSuggestions');
    const selectedUserId = document.getElementById('selectedUserId');
    const selectedMember = document.getElementById('selectedMember');

    if (memberSearch && memberSuggestions && selectedUserId && selectedMember) {
        let memberTimer = null;
        let memberController = null;
        let memberSequence = 0;

        function hideMemberSuggestions() {
            memberSuggestions.replaceChildren();
            memberSuggestions.style.display = 'none';
        }

        function clearMemberSelection() {
            selectedUserId.value = '';
            selectedMember.replaceChildren();
            selectedMember.style.display = 'none';
        }

        function selectMember(member) {
            selectedUserId.value = member.id;
            memberSearch.value = member.full_name;

            hideMemberSuggestions();
            selectedMember.replaceChildren();

            const card = createElement(
                'div',
                'alert alert-success d-flex justify-content-between align-items-start gap-3 mb-0'
            );

            const details = document.createElement('div');

            details.append(
                createElement('div', 'fw-semibold', member.full_name),
                createElement(
                    'div',
                    'small',
                    [
                        member.mobile,
                        member.email,
                        member.membership_number
                            ? `Membership: ${member.membership_number}`
                            : null
                    ].filter(Boolean).join(' · ')
                )
            );

            const remove = document.createElement('button');

            remove.type = 'button';
            remove.className = 'btn-close';
            remove.setAttribute('aria-label', 'Remove selected member');

            remove.addEventListener('click', function () {
                clearMemberSelection();
                memberSearch.value = '';
                memberSearch.focus();
            });

            card.append(details, remove);
            selectedMember.appendChild(card);
            selectedMember.style.display = 'block';
        }

        memberSearch.addEventListener('input', function () {
            clearMemberSelection();
            clearTimeout(memberTimer);

            const term = memberSearch.value.trim();
            const sequence = ++memberSequence;

            if (memberController) {
                memberController.abort();
            }

            if (term.length < 2) {
                hideMemberSuggestions();
                return;
            }

            memberTimer = setTimeout(async function () {
                memberController = new AbortController();

                try {
                    const url = new URL(
                        @json(route('orders.search')),
                        window.location.origin
                    );

                    url.searchParams.set('type', 'member');
                    url.searchParams.set('q', term);

                    const members = await fetchSuggestions(
                        url,
                        memberController.signal
                    );

                    if (sequence !== memberSequence) {
                        return;
                    }

                    hideMemberSuggestions();

                    if (!members.length) {
                        memberSuggestions.appendChild(
                            createElement(
                                'div',
                                'list-group-item text-muted',
                                'No matching members found.'
                            )
                        );

                        memberSuggestions.style.display = 'block';
                        return;
                    }

                    members.forEach(function (member) {
                        const button = document.createElement('button');

                        button.type = 'button';
                        button.className =
                            'list-group-item list-group-item-action';

                        button.append(
                            createElement('div', 'fw-semibold', member.full_name),
                            createElement(
                                'small',
                                'text-muted d-block',
                                [
                                    member.mobile,
                                    member.email,
                                    member.membership_number
                                        ? `Membership: ${member.membership_number}`
                                        : null
                                ].filter(Boolean).join(' · ')
                            )
                        );

                        button.addEventListener('click', function () {
                            selectMember(member);
                        });

                        memberSuggestions.appendChild(button);
                    });

                    memberSuggestions.style.display = 'block';
                } catch (error) {
                    if (error.name !== 'AbortError') {
                        hideMemberSuggestions();
                    }
                }
            }, 250);
        });

        document.addEventListener('click', function (event) {
            if (
                !memberSuggestions.contains(event.target) &&
                event.target !== memberSearch
            ) {
                hideMemberSuggestions();
            }
        });

        // Validation fail er por selected member restore
        const preselectedMember = @json($selectedMember ?? null);

        if (preselectedMember) {
            selectMember(preselectedMember);
        }
    }

    // Final form validation

    form.addEventListener('submit', function (event) {
        const errors = [];
        let hasPositiveQuantity = false;

        selectedProducts.forEach(function (product) {
            const quantity = product.quantity;

            if (quantity > product.stock_quantity) {
                errors.push(
                    `${product.name}: quantity exceeds available stock.`
                );
            }

            if (quantity > 0) {
                hasPositiveQuantity = true;
            }

            if (product.track_serials && quantity > 0) {
                const serials = product.serials
                    .slice(0, quantity)
                    .map(value => String(value ?? '').trim());

                if (
                    serials.length !== quantity ||
                    serials.some(value => value === '')
                ) {
                    errors.push(
                        `${product.name}: enter exactly ${quantity} serial number(s).`
                    );
                }

                if (new Set(serials.filter(Boolean)).size !== serials.filter(Boolean).length) {
                    errors.push(
                        `${product.name}: duplicate serial numbers are not allowed.`
                    );
                }
            }
        });

        if (selectedProducts.size === 0) {
            errors.push('Please select at least one product.');
        }

        if (!hasPositiveQuantity) {
            errors.push('At least one product quantity must be greater than zero.');
        }

        if (errors.length) {
            event.preventDefault();
            alert([...new Set(errors)].join('\n'));
        }
    });

    // Old selected products restore (validation fail er por)

    async function restoreOldItems() {
        const ids = Object.keys(oldItems || {});

        if (!ids.length) {
            return;
        }

        try {
            const url = new URL(@json(route('orders.search')), window.location.origin);

            url.searchParams.set('type', 'product');
            ids.forEach(id => url.searchParams.append('ids[]', id));

            const products = await fetchSuggestions(url);

            ids.forEach(function (id) {
                const data = products.find(p => String(p.id) === String(id));

                if (!data) {
                    return;
                }

                const product = normalizeProduct(data);
                const old = oldItems[id] || {};

                let quantity = parseInt(old.quantity ?? 1, 10);

                if (!Number.isInteger(quantity) || quantity < 0) {
                    quantity = 0;
                }

                quantity = Math.min(quantity, product.stock_quantity);

                selectedProducts.set(String(product.id), {
                    ...product,
                    quantity,
                    serials: Array.from(
                        { length: quantity },
                        (_, i) => old.serials?.[i] ?? ''
                    )
                });
            });

            renderSelectedProducts();
        } catch (error) {
            console.error('Unable to restore selected products.', error);
        }
    }

    // Initial state
    renderSelectedProducts();
    restoreOldItems();
});
</script>
@endpush