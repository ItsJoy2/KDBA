<!-- ===== 1. HEADER ===== -->
<nav class="navbar navbar-expand-lg sticky-top">
  <div class="container">
    <a class="navbar-brand" href="{{ route('home') }}">
      <span class="logo">
        <img src="{{ asset('assets/images/KDBA.png') }}" alt="Khagrachari District Bar Association logo" width="44" height="44">
      </span>
      <span>Khagrachari District Bar Association<small>ESTABLISHED FOR JUSTICE &amp; THE RULE OF LAW</small></span>
    </a>
    <button class="navbar-toggler" data-bs-toggle="collapse" data-bs-target="#menu" aria-label="Menu"><i class="bi bi-list fs-3"></i></button>
    <div class="collapse navbar-collapse" id="menu">
      <ul class="navbar-nav mx-auto">
        <li class="nav-item"><a class="nav-link" href="{{ route('home') }}#home">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('about') }}">About</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('committee') }}">Committee</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('notices') }}">Notices</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('events') }}">Events</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('articles') }}">Articles</a></li>
        <li class="nav-item"><a class="nav-link" href="{{ route('contact') }}">Contact</a></li>
      </ul>

        {{-- Theme Switcher + Login Button --}}
        <div class="d-flex align-items-center gap-2">

            {{-- Theme Switcher --}}
            <div class="dropdown">
                <button class="theme-btn dropdown-toggle"
                    data-bs-toggle="dropdown"
                    aria-label="Choose theme">

                    <i id="themeIcon" class="bi bi-circle-half"></i>
                </button>

                <ul class="dropdown-menu dropdown-menu-end">

                    <li>
                        <button class="dropdown-item" data-theme="light">
                            <i class="bi bi-sun-fill me-2"></i> Light
                        </button>
                    </li>

                    <li>
                        <button class="dropdown-item" data-theme="dark">
                            <i class="bi bi-moon-stars-fill me-2"></i> Dark
                        </button>
                    </li>

                    <li>
                        <button class="dropdown-item" data-theme="auto">
                            <i class="bi bi-circle-half me-2"></i> Auto
                        </button>
                    </li>

                </ul>
            </div>

            {{-- Login Button --}}
            <a href="{{ route('login') }}" class="btn btn-gold login-btn"><i class="bi bi-person-lock me-1"></i> Login</a>

        </div>
    </div>
  </div>
</nav>