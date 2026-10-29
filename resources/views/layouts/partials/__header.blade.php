
<nav class="app-header navbar navbar-expand bg-body">
    <div class="container-fluid">

        {{-- Left Navbar --}}
        <ul class="navbar-nav">
            <li class="nav-item">
                <a class="nav-link"
                   data-lte-toggle="sidebar"
                   href="#"
                   role="button"
                   aria-label="Toggle sidebar">
                    <i class="bi bi-list"></i>
                </a>
            </li>

            {{-- <li class="nav-item d-none d-md-block">
                <a href="{{ route('dashboard') }}" class="nav-link">
                    <i class="bi bi-grid-1x2 me-1"></i>
                    Dashboard
                </a>
            </li> --}}
        </ul>

        {{-- Search --}}
        {{-- <form class="navbar-search d-none d-xl-block ms-3"
              role="search"
              action="{{ url('/search') }}"
              method="GET">
            <label for="navbar-search-input" class="visually-hidden">
                Search
            </label>

            <div class="navbar-search-field">
                <input
                    type="search"
                    id="navbar-search-input"
                    name="q"
                    class="form-control"
                    placeholder="Search..."
                >
                <button class="navbar-search-submit" type="submit"
                        aria-label="Submit search">
                    <i class="bi bi-search"></i>
                </button>
            </div>
        </form> --}}

        {{-- Right Navbar --}}
        <ul class="navbar-nav ms-auto">

            {{-- Notifications --}}
            {{-- <li class="nav-item dropdown">
                <a class="nav-link"
                   href="#"
                   data-bs-toggle="dropdown"
                   aria-label="Notifications">
                    <i class="bi bi-bell-fill"></i>
                    <span class="navbar-badge badge text-bg-warning">15</span>
                </a>

                <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header">
                        15 Notifications
                    </span>

                    <div class="dropdown-divider"></div>

                    <a href="#" class="dropdown-item">
                        <i class="bi bi-envelope me-2"></i>
                        New messages
                    </a>

                    <div class="dropdown-divider"></div>

                    <a href="#" class="dropdown-item">
                        <i class="bi bi-people me-2"></i>
                        New members
                    </a>

                    <div class="dropdown-divider"></div>

                    <a href="#" class="dropdown-item dropdown-footer">
                        See All Notifications
                    </a>
                </div>
            </li> --}}

            {{-- Theme --}}
            <li class="nav-item dropdown">
                <a class="nav-link"
                   href="#"
                   id="bd-theme"
                   data-bs-toggle="dropdown"
                   aria-label="Change color theme">
                    <i class="bi bi-circle-half"></i>
                </a>

                <ul class="dropdown-menu dropdown-menu-end"
                    aria-labelledby="bd-theme">
                    @foreach (['light' => 'Light', 'dark' => 'Dark', 'auto' => 'Auto'] as $value => $label)
                        <li>
                            <button type="button"
                                    class="dropdown-item"
                                    data-bs-theme-value="{{ $value }}">
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>
            </li>


            {{-- User Menu --}}
            <li class="nav-item dropdown user-menu">
                <a href="#"
                class="nav-link dropdown-toggle d-flex align-items-center gap-2"
                data-bs-toggle="dropdown"
                aria-expanded="false">

                    @if ($headerPhotoUrl)
                        <img
                            src="{{ $headerPhotoUrl }}"
                            class="user-image rounded-circle shadow"
                            alt="{{ $headerUserName }}"
                            width="32"
                            height="32"
                            style="object-fit: cover;"
                        >
                    @else
                        <span
                            class="user-image rounded-circle shadow bg-primary text-white
                                d-inline-flex align-items-center justify-content-center
                                fw-bold"
                            style="width: 32px; height: 32px; font-size: 12px;"
                            aria-label="{{ $headerUserName }}"
                        >{{ $headerInitials }}</span>
                    @endif

                    <span class="d-none d-md-inline">
                        {{ $headerUserName }}
                    </span>
                </a>

                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">

                    {{-- User Header --}}
                    <li class="user-header text-bg-primary">

                        @if ($headerPhotoUrl)
                            <img
                                src="{{ $headerPhotoUrl }}"
                                class="rounded-circle shadow"
                                alt="{{ $headerUserName }}"
                                width="90"
                                height="90"
                                style="object-fit: cover;"
                            >
                        @else
                            <span
                                class="rounded-circle shadow bg-white text-primary
                                    d-inline-flex align-items-center justify-content-center
                                    fw-bold"
                                style="width: 90px; height: 90px; font-size: 28px;"
                                aria-label="{{ $headerUserName }}"
                            >{{ $headerInitials }}</span>
                        @endif

                        <p>
                            {{ $headerUserName }}
                            <small>
                                {{ auth()->user()?->member_status
                                    ? str_replace('_', ' ', ucfirst(auth()->user()->member_status))
                                    : 'Member' }}
                            </small>
                        </p>
                    </li>

                    {{-- User Footer --}}
                    <li class="user-footer d-flex justify-content-between align-items-center gap-2">
                        <a href="{{ url('/profile') }}"
                        class="btn btn-outline-secondary">
                            <i class="bi bi-person me-1"></i>
                            Profile
                        </a>

                        <form action="{{ url('/logout') }}" method="POST">
                            @csrf

                            <button type="submit" class="btn btn-outline-danger">
                                <i class="bi bi-box-arrow-right me-1"></i>
                                Sign out
                            </button>
                        </form>
                    </li>

                </ul>
            </li>


        </ul>
    </div>
</nav>
