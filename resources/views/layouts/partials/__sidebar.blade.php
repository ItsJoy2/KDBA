
<aside class="app-sidebar bg-body-secondary shadow"
       data-bs-theme="dark">

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

    Sidebar Search
    {{-- <div class="sidebar-search">
        <label for="sidebar-search-input" class="visually-hidden">
            Filter menu
        </label>

        <input
            type="search"
            id="sidebar-search-input"
            class="form-control form-control-sm"
            placeholder="Filter menu..."
            autocomplete="off"
            data-lte-toggle="sidebar-search"
            data-lte-target="#navigation"
        >

        <p class="fs-7 text-secondary mt-2 mb-0"
           data-lte-search-empty
           role="status"
           hidden>
            No matching pages.
        </p>
    </div> --}}

    {{-- Sidebar Menu --}}
    <div class="sidebar-wrapper">
        <nav class="mt-2" aria-label="Main navigation">
            <ul class="nav sidebar-menu flex-column"
                data-lte-toggle="treeview"
                data-accordion="false"
                id="navigation">

                {{-- <li class="nav-header">MAIN MENU</li> --}}

                <li class="nav-item">
                    <a href="{{ route('dashboard') }}"
                       class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                        <i class="nav-icon bi bi-speedometer"></i>
                        <p>Dashboard</p>
                    </a>
                </li>

                @if(auth()->user()->hasPermission('users.view'))
                    <li class="nav-item">
                        <a href="{{ route('users.index') }}"
                        class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}">

                            <i class="nav-icon bi bi-people"></i>
                            <p>Users</p>
                        </a>
                    </li>
                @endif

                @if ($hasAssistants)
                    <li class="nav-item">
                        <a
                            href="{{ route('assistants.index') }}"
                            class="nav-link {{ request()->routeIs('assistants.*') ? 'active' : '' }}"
                        >
                            <i class="bi bi-people me-2"></i>
                            <span>Assistants</span>
                        </a>
                    </li>
                @endif

                @if (auth()->user()->hasPermission('roles.view'))
                    <li class="nav-item">
                        <a href="{{ route('roles.index') }}"
                            class="nav-link {{ request()->routeIs('roles.*') ? 'active' : '' }}">

                            <i class="nav-icon bi bi-shield-lock"></i>
                            <p>Roles & Permissions</p>
                        </a>
                    </li>
                @endif

                {{-- Widgets --}}
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-box-seam"></i>
                        <p>
                            Widgets
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/widgets/small-box') }}"
                               class="nav-link">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Small Box</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('/widgets/cards') }}"
                               class="nav-link">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Cards</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Forms --}}
                <li class="nav-item">
                    <a href="#" class="nav-link">
                        <i class="nav-icon bi bi-pencil-square"></i>
                        <p>
                            Forms
                            <i class="nav-arrow bi bi-chevron-right"></i>
                        </p>
                    </a>

                    <ul class="nav nav-treeview">
                        <li class="nav-item">
                            <a href="{{ url('/forms/elements') }}"
                               class="nav-link">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Form Elements</p>
                            </a>
                        </li>

                        <li class="nav-item">
                            <a href="{{ url('/forms/validation') }}"
                               class="nav-link">
                                <i class="nav-icon bi bi-circle"></i>
                                <p>Validation</p>
                            </a>
                        </li>
                    </ul>
                </li>

                {{-- Tables --}}
                <li class="nav-item">
                    <a href="{{ url('/tables') }}" class="nav-link">
                        <i class="nav-icon bi bi-table"></i>
                        <p>Tables</p>
                    </a>
                </li>

                {{-- Charts --}}
                <li class="nav-item">
                    <a href="{{ url('/charts') }}" class="nav-link">
                        <i class="nav-icon bi bi-graph-up"></i>
                        <p>Charts</p>
                    </a>
                </li>

                <li class="nav-header">PAGES</li>

                <li class="nav-item">
                    <a href="{{ url('/profile') }}" class="nav-link">
                        <i class="nav-icon bi bi-person"></i>
                        <p>Profile</p>
                    </a>
                </li>

                <li class="nav-item">
                    <a href="{{ url('/settings') }}" class="nav-link">
                        <i class="nav-icon bi bi-gear"></i>
                        <p>Settings</p>
                    </a>
                </li>

            </ul>
        </nav>
    </div>
</aside>
