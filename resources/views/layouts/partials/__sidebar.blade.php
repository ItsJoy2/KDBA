
<aside class="app-sidebar bg-body-secondary shadow" data-bs-theme="dark">

    {{-- Brand --}}
    <div class="sidebar-brand">
        <a href="{{ route('dashboard') }}" class="brand-link">
            <img
                src="{{ asset('assets/adminlte/img/AdminLTELogo.png') }}"
                alt="KBA"
                class="brand-image opacity-75 shadow"
            >
            <span class="brand-text fw-light">KBA</span>
        </a>
    </div>

    {{-- Sidebar Menu --}}
    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false"
                id="navigation">

                {{-- Dashboard --}}
                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                {{-- Users --}}
                @if(auth()->user()->hasPermission('users.view'))
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                           class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-people"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endif

                {{-- Assistants --}}
                @if($hasAssistants)
                    <li class="nav-item">
                        <a href="{{ route('assistants.index') }}"
                           class="nav-link {{ request()->routeIs('assistants.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-person-workspace"></i>
                            <p>Assistants</p>
                        </a>
                    </li>
                @endif

                {{-- Product Management --}}
                @if(
                    auth()->user()->hasPermission('products.view') ||
                    auth()->user()->hasPermission('products.create')
                )
                    <li class="nav-item {{ request()->routeIs('products.*') ? 'menu-open' : '' }}">
                        <a href="#"
                           class="nav-link {{ request()->routeIs('products.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-box-seam-fill"></i>
                            <p>
                                Product Management
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @if(auth()->user()->hasPermission('products.view'))
                                <li class="nav-item">
                                    <a href="{{ route('products.index') }}"
                                       class="nav-link {{ request()->routeIs('products.index', 'products.show') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>All Products</p>
                                    </a>
                                </li>
                            @endif

                            @if(auth()->user()->hasPermission('products.create'))
                                <li class="nav-item">
                                    <a href="{{ route('products.create') }}"
                                       class="nav-link {{ request()->routeIs('products.create') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Add Product</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Shop --}}
                @unless(auth()->user()->roles()->where('slug', 'super-admin')->exists())
                    <li class="nav-item">
                        <a href="{{ route('shop.index') }}"
                           class="nav-link {{ request()->routeIs('shop.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shop"></i>
                            <p>Shop</p>
                        </a>
                    </li>
                @endunless

                {{-- Order Management --}}
                @if(
                    auth()->user()->hasPermission('orders.view') ||
                    auth()->user()->hasPermission('products.buy')
                )
                    <li class="nav-item {{ request()->routeIs('orders.*') ? 'menu-open' : '' }}">
                        <a href="#"
                           class="nav-link {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-cart-fill"></i>
                            <p>
                                Order Management
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">
                            @if(auth()->user()->hasPermission('orders.view'))
                                <li class="nav-item">
                                    <a href="{{ route('orders.index') }}"
                                       class="nav-link {{ request()->routeIs('orders.index', 'orders.show') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>All Orders</p>
                                    </a>
                                </li>
                            @endif

                            @if(auth()->user()->hasPermission('products.buy_for_others'))
                                <li class="nav-item">
                                    <a href="{{ route('orders.create') }}"
                                       class="nav-link {{ request()->routeIs('orders.create') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Place Order</p>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif

                {{-- Membership & Payment Management --}}
                @if(
                    auth()->user()->hasPermission('fees.manage') ||
                    auth()->user()->hasPermission('gateways.manage') ||
                    auth()->user()->hasPermission('invoices.view') ||
                    auth()->user()->hasPermission('transactions.view')
                )
                    <li class="nav-header">MEMBERSHIP & FINANCE</li>

                    <li class="nav-item {{ request()->routeIs('admin.membership-fees.*', 'admin.payment-gateways.*', 'admin.invoices.*', 'admin.payment-transactions.*') ? 'menu-open' : '' }}">

                        <a href="#"
                           class="nav-link {{ request()->routeIs('admin.membership-fees.*', 'admin.payment-gateways.*', 'admin.invoices.*', 'admin.payment-transactions.*') ? 'active' : '' }}">

                            <i class="nav-icon bi bi-cash-stack"></i>

                            <p>
                                Membership & Finance
                                <i class="nav-arrow bi bi-chevron-right"></i>
                            </p>
                        </a>

                        <ul class="nav nav-treeview">

                            {{-- Membership Fee Settings --}}
                            @if(auth()->user()->hasPermission('fees.manage'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.membership-fees.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.membership-fees.*') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Membership Fee Settings</p>
                                    </a>
                                </li>
                            @endif

                            {{-- Payment Gateways --}}
                            @if(auth()->user()->hasPermission('gateways.manage'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.payment-gateways.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.payment-gateways.*') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Payment Gateways</p>
                                    </a>
                                </li>
                            @endif

                            {{-- All Invoices --}}
                            @if(auth()->user()->hasPermission('invoices.view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.invoices.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>All Invoices</p>
                                    </a>
                                </li>
                            @endif

                            {{-- Payment Verification --}}
                            @if(auth()->user()->hasPermission('transactions.view'))
                                <li class="nav-item">
                                    <a href="{{ route('admin.payment-transactions.index') }}"
                                       class="nav-link {{ request()->routeIs('admin.payment-transactions.*') ? 'active' : '' }}">
                                        <i class="nav-icon bi bi-circle"></i>
                                        <p>Payment Verification</p>
                                    </a>
                                </li>
                            @endif

                        </ul>
                    </li>
                @endif

                {{-- Roles & Permissions --}}
                @if(auth()->user()->hasPermission('roles.view'))
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}"
                           class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-shield-lock"></i>
                            <p>Roles & Permissions</p>
                        </a>
                    </li>
                @endif

                {{-- Cache Management --}}
                @if(
                    auth()->user()->hasPermission('cache.clear') ||
                    auth()->user()->roles()->where('slug', 'super-admin')->exists()
                )
                    <li class="nav-item">
                        <a href="{{ route('system.cache.index') }}"
                           class="nav-link {{ request()->routeIs('admin.system.cache.*') ? 'active' : '' }}">
                            <i class="nav-icon bi bi-arrow-repeat"></i>
                            <p>Cache Management</p>
                        </a>
                    </li>
                @endif

            </ul>
        </nav>
    </div>
</aside>