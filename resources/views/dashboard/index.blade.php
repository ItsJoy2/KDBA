
@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')

{{-- Page Header --}}
<div class="app-content-header">
    <div class="container-fluid">
        <div class="row align-items-center">
            <div class="col-sm-6">
                <h1 class="mb-0 fs-3">Dashboard</h1>
            </div>

            <div class="col-sm-6">
                <ol class="breadcrumb float-sm-end mb-0">
                    <li class="breadcrumb-item">
                        <a href="{{ route('dashboard') }}">Home</a>
                    </li>
                    <li class="breadcrumb-item active">Dashboard</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="app-content">
    <div class="container-fluid">

        {{-- Summary Cards --}}
        {{-- <div class="row">

            @php
                $stats = [
                    [
                        'title' => 'CPU Traffic',
                        'value' => '10%',
                        'icon' => 'bi-gear-fill',
                        'color' => 'primary',
                    ],
                    [
                        'title' => 'Likes',
                        'value' => '41,410',
                        'icon' => 'bi-hand-thumbs-up-fill',
                        'color' => 'danger',
                    ],
                    [
                        'title' => 'Sales',
                        'value' => '760',
                        'icon' => 'bi-cart-fill',
                        'color' => 'success',
                    ],
                    [
                        'title' => 'New Members',
                        'value' => '2,000',
                        'icon' => 'bi-people-fill',
                        'color' => 'warning',
                    ],
                ];
            @endphp

            @foreach ($stats as $stat)
                <div class="col-12 col-sm-6 col-md-3">
                    <div class="info-box">
                        <span class="info-box-icon text-bg-primary shadow-sm">
                            <i class="bi {{ $stat['icon'] }}"></i>
                        </span>

                        <div class="info-box-content">
                            <span class="info-box-text">
                                {{ $stat['title'] }}
                            </span>
                            <span class="info-box-number">
                                {{ $stat['value'] }}
                            </span>
                        </div>
                    </div>
                </div>
            @endforeach

        </div> --}}

        {{-- Summary Cards --}}
    <div class="row">

        @if ($canViewUsers)
            {{-- Total Users --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-primary shadow-sm">
                        <i class="bi bi-people-fill"></i>
                    </span>

                    <div class="info-box-content">
                        <span class="info-box-text">Total Users</span>
                        <span class="info-box-number">
                            {{ number_format($totalUsers) }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Active Members --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-success shadow-sm">
                        <i class="bi bi-person-check-fill"></i>
                    </span>

                    <div class="info-box-content">
                        <span class="info-box-text">Active Members</span>
                        <span class="info-box-number">
                            {{ number_format($activeUsers) }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        @if ($canApproveUsers)
            {{-- Pending Approvals --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-warning shadow-sm">
                        <i class="bi bi-person-exclamation"></i>
                    </span>

                    <div class="info-box-content">
                        <span class="info-box-text">Pending Approvals</span>
                        <span class="info-box-number">
                            {{ number_format($pendingUsers) }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

        @if ($canChangeMemberStatus)
            {{-- Inactive Members --}}
            <div class="col-12 col-sm-6 col-md-3">
                <div class="info-box">
                    <span class="info-box-icon text-bg-danger shadow-sm">
                        <i class="bi bi-person-x-fill"></i>
                    </span>

                    <div class="info-box-content">
                        <span class="info-box-text">Inactive Members</span>
                        <span class="info-box-number">
                            {{ number_format($inactiveUsers) }}
                        </span>
                    </div>
                </div>
            </div>
        @endif

    </div>

        {{-- Monthly Sales --}}
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">

                    <div class="card-header">
                        <h3 class="card-title">Monthly Recap Report</h3>

                        <div class="card-tools">
                            <button type="button"
                                    class="btn btn-tool"
                                    data-lte-toggle="card-collapse"
                                    aria-label="Collapse card">
                                <i data-lte-icon="expand" class="bi bi-plus-lg"></i>
                                <i data-lte-icon="collapse" class="bi bi-dash-lg"></i>
                            </button>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="row">

                            <div class="col-md-8">
                                <p class="text-center">
                                    <strong>Sales: January - July</strong>
                                </p>

                                <div style="height: 300px">
                                    <canvas id="sales-chart"
                                            role="img"
                                            aria-label="Monthly sales chart"></canvas>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <p class="text-center">
                                    <strong>Goal Completion</strong>
                                </p>

                                @php
                                    $goals = [
                                        ['label' => 'Add Products to Cart', 'done' => 160, 'total' => 200, 'color' => 'primary'],
                                        ['label' => 'Complete Purchase', 'done' => 310, 'total' => 400, 'color' => 'danger'],
                                        ['label' => 'Visit Premium Page', 'done' => 480, 'total' => 800, 'color' => 'success'],
                                        ['label' => 'Send Inquiries', 'done' => 250, 'total' => 500, 'color' => 'warning'],
                                    ];
                                @endphp

                                @foreach ($goals as $goal)
                                    <div class="progress-group mb-4">
                                        {{ $goal['label'] }}

                                        <span class="float-end">
                                            <b>{{ $goal['done'] }}</b>/{{ $goal['total'] }}
                                        </span>

                                        <div class="progress progress-sm">
                                            <div
                                                class="progress-bar text-bg-{{ $goal['color'] }}"
                                                style="width: {{ ($goal['done'] / $goal['total']) * 100 }}%">
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                        </div>
                    </div>

                    {{-- Revenue Summary --}}
                    <div class="card-footer bg-body">
                        <div class="row text-center">

                            <div class="col-6 col-md-3 mb-3">
                                <span class="text-success">
                                    <i class="bi bi-caret-up-fill"></i> 17%
                                </span>
                                <h5 class="fw-bold mb-0">$35,210.43</h5>
                                <small>TOTAL REVENUE</small>
                            </div>

                            <div class="col-6 col-md-3 mb-3">
                                <span class="text-secondary">0%</span>
                                <h5 class="fw-bold mb-0">$10,390.90</h5>
                                <small>TOTAL COST</small>
                            </div>

                            <div class="col-6 col-md-3 mb-3">
                                <span class="text-success">
                                    <i class="bi bi-caret-up-fill"></i> 20%
                                </span>
                                <h5 class="fw-bold mb-0">$24,813.53</h5>
                                <small>TOTAL PROFIT</small>
                            </div>

                            <div class="col-6 col-md-3 mb-3">
                                <span class="text-danger">
                                    <i class="bi bi-caret-down-fill"></i> 18%
                                </span>
                                <h5 class="fw-bold mb-0">1,200</h5>
                                <small>GOAL COMPLETIONS</small>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        {{-- Orders and Browser Chart --}}
        <div class="row">

            <div class="col-lg-8">
                <div class="card mb-4">

                    <div class="card-header">
                        <h3 class="card-title">Latest Orders</h3>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover m-0">
                                <thead>
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Item</th>
                                        <th>Status</th>
                                        <th>Price</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @php
                                        $orders = [
                                            ['id' => 'OR9842', 'item' => 'Call of Duty IV', 'status' => 'Shipped', 'color' => 'success', 'price' => '$120'],
                                            ['id' => 'OR1848', 'item' => 'Samsung Smart TV', 'status' => 'Pending', 'color' => 'warning', 'price' => '$850'],
                                            ['id' => 'OR7429', 'item' => 'iPhone 6 Plus', 'status' => 'Delivered', 'color' => 'danger', 'price' => '$450'],
                                            ['id' => 'OR7428', 'item' => 'Samsung Smart TV', 'status' => 'Processing', 'color' => 'info', 'price' => '$700'],
                                        ];
                                    @endphp

                                    @foreach ($orders as $order)
                                        <tr>
                                            <td>{{ $order['id'] }}</td>
                                            <td>{{ $order['item'] }}</td>
                                            <td>
                                                <span class="badge text-bg-{{ $order['color'] }}">
                                                    {{ $order['status'] }}
                                                </span>
                                            </td>
                                            <td>{{ $order['price'] }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card-footer clearfix">
                        <a href="{{ url('/orders/create') }}"
                           class="btn btn-sm btn-primary">
                            Place New Order
                        </a>

                        <a href="{{ url('/orders') }}"
                           class="btn btn-sm btn-secondary float-end">
                            View All Orders
                        </a>
                    </div>

                </div>
            </div>

            {{-- Browser Usage --}}
            <div class="col-lg-4">
                <div class="card mb-4">
                    <div class="card-header">
                        <h3 class="card-title">Browser Usage</h3>
                    </div>

                    <div class="card-body">
                        <div style="height: 280px">
                            <canvas id="pie-chart"
                                    role="img"
                                    aria-label="Browser usage chart"></canvas>
                        </div>
                    </div>
                </div>

                {{-- Additional Info Boxes --}}
                <div class="info-box text-bg-warning mb-3">
                    <span class="info-box-icon">
                        <i class="bi bi-tag-fill"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Inventory</span>
                        <span class="info-box-number">5,200</span>
                    </div>
                </div>

                <div class="info-box text-bg-success mb-3">
                    <span class="info-box-icon">
                        <i class="bi bi-heart-fill"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Mentions</span>
                        <span class="info-box-number">92,050</span>
                    </div>
                </div>

                <div class="info-box text-bg-info mb-3">
                    <span class="info-box-icon">
                        <i class="bi bi-chat-fill"></i>
                    </span>
                    <div class="info-box-content">
                        <span class="info-box-text">Direct Messages</span>
                        <span class="info-box-number">163,921</span>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Monthly Sales Chart
    const salesCanvas = document.getElementById('sales-chart');

    if (salesCanvas) {
        new Chart(salesCanvas, {
            type: 'line',
            data: {
                labels: ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul"],
                datasets: [
                    {
                        label: 'Digital Goods',
                        data: [28, 48, 40, 19, 86, 27, 90],
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.15)',
                        fill: true,
                        tension: 0.4
                    },
                    {
                        label: 'Electronics',
                        data: [65, 59, 80, 81, 56, 55, 40],
                        borderColor: '#20c997',
                        backgroundColor: 'rgba(32, 201, 151, 0.15)',
                        fill: true,
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // Browser Usage Chart
    const browserCanvas = document.getElementById('pie-chart');

    if (browserCanvas) {
        new Chart(browserCanvas, {
            type: 'doughnut',
            data: {
                labels: ['Chrome', 'Edge', 'Firefox', 'Safari', 'Opera', 'IE'],
                datasets: [{
                    data: [700, 500, 400, 600, 300, 100],
                    backgroundColor: [
                        '#0d6efd',
                        '#20c997',
                        '#ffc107',
                        '#d63384',
                        '#6f42c1',
                        '#adb5bd'
                    ]
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '65%'
            }
        });
    }

});
</script>
@endpush
