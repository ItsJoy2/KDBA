<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'Khagrachari District Bar Association')</title>

    <meta name="description"
        content="@yield('description', 'Khagrachari District Bar Association official website.')">

            {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="icon" href="{{ asset('assets/images/KDBA.png') }}" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">
    <meta name="theme-color" content="#0c1e3a">

    {{-- Theme --}}
    <script>
        (function () {
            let theme = localStorage.getItem('theme') || 'auto';

            let mode = theme === 'auto'
                ? (matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light')
                : theme;

            document.documentElement.setAttribute('data-bs-theme', mode);
        })();
    </script>

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css2?family=Figtree:wght@400;500;600&family=Newsreader:ital,wght@0,400;0,600;1,400&display=swap"
        rel="stylesheet">

    {{-- Bootstrap --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    {{-- Bootstrap Icons --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css"
        rel="stylesheet">
        
    @include('layouts.partials.__theme-init')
    {{-- Custom CSS --}}
    <link rel="stylesheet" href="{{ asset('assets/css/cms.css') }}">

    @stack('styles')
</head>

<body data-view="@yield('view')"
    {{-- data-article-url="{{ route('articles.show') }}"
    data-articles-url="{{ route('articles') }}"> --}}

    {{-- Navbar --}}
    @include('cms.components.navbar')

    {{-- Main Content --}}
    <main>
        @yield('content')
    </main>

    {{-- Footer --}}
    @include('cms.components.footer')

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    {{-- Custom JS --}}
    <script src="{{ asset('assets/js/cms.js') }}"></script>

    @stack('scripts')

</body>
</html>