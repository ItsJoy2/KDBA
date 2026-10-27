<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

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