<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

                {{-- Favicon --}}
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('images/favicon-32x32.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('images/favicon-16x16.png') }}">
    <link rel="icon" href="{{ asset('assets/images/KDBA.png') }}" sizes="any">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('images/apple-touch-icon.png') }}">
    <link rel="manifest" href="{{ asset('images/site.webmanifest') }}">
    <meta name="theme-color" content="#0c1e3a">

    <title>@yield('title', 'Login') | KBA</title>

    @include('layouts.partials.__theme-init')
    @include('layouts.partials.__style')
    @stack('styles')
</head>

<body class="login-page bg-body-secondary">

        {{-- Main Content --}}
        <main class="login-box">

            @yield('content')

        </main>

    {{-- JavaScript --}}
    @include('layouts.partials.__script')
    @stack('scripts')

</body>
</html>