<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Admin Dashboard') | Khagrachari Bar Association</title>

    @include('layouts.partials.__theme-init')
    @include('layouts.partials.__style')
    @stack('styles')
</head>

<body class="layout-fixed sidebar-expand-lg bg-body-tertiary">

    <div class="app-wrapper">

        {{-- Header --}}
        @include('layouts.partials.__header')

        {{-- Sidebar --}}
        @include('layouts.partials.__sidebar')

        {{-- Main Content --}}
        <main class="app-main">

            @yield('content')

        </main>

        {{-- Footer --}}
        @include('layouts.partials.__footer')

    </div>

    {{-- JavaScript --}}
    @include('layouts.partials.__script')
    @stack('scripts')

</body>
</html>