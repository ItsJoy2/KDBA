@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<div class="container-fluid">

    {{-- =====================================================
        PAGE HEADER
    ====================================================== --}}
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 mt-4">Dashboard</h4>
            <p class="text-muted mb-0">
                Welcome back, {{ auth()->user()->full_name ?? 'User' }}.
                Here is your system overview.
            </p>
        </div>

        <div class="text-muted small mt-2 mt-md-0">
            <i class="bi bi-calendar3 me-1"></i>
            {{ now()->format('d M Y') }}
        </div>
    </div>

    {{-- =====================================================
        MEMBERSHIP STATISTICS
    ====================================================== --}}
    @if ($canViewUsers || $canApproveUsers || $canChangeMemberStatus)
        <h6 class="text-uppercase text-muted fw-semibold mb-3">
            Membership Overview
        </h6>

        <div class="row g-3 mb-4">

            @if ($canViewUsers)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small mb-2">Total Members</div>
                                    <h3 class="fw-bold mb-0">
                                        {{ number_format($totalUsers) }}
                                    </h3>
                                </div>
                                <div class="rounded-circle bg-primary bg-opacity-10 px-3 py-2">
                                    <i class="bi bi-people text-primary fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small mb-2">Active Members</div>
                                    <h3 class="fw-bold text-success mb-0">
                                        {{ number_format($activeUsers) }}
                                    </h3>
                                </div>
                                <div class="rounded-circle bg-success bg-opacity-10 px-3 py-2">
                                    <i class="bi bi-person-check text-success fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($canApproveUsers)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small mb-2">Pending Approval</div>
                                    <h3 class="fw-bold text-warning mb-0">
                                        {{ number_format($pendingUsers) }}
                                    </h3>
                                </div>
                                <div class="rounded-circle bg-warning bg-opacity-10 px-3 py-2">
                                    <i class="bi bi-hourglass-split text-warning fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            @if ($canChangeMemberStatus)
                <div class="col-xl-3 col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="text-muted small mb-2">Inactive / Suspended</div>
                                    <h3 class="fw-bold text-danger mb-0">
                                        {{ number_format($inactiveUsers) }}
                                    </h3>
                                </div>
                                <div class="rounded-circle bg-danger bg-opacity-10 px-3 py-2">
                                    <i class="bi bi-person-x text-danger fs-4"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

        </div>
    @endif

    {{-- =====================================================
        ORDER STATISTICS
    ====================================================== --}}
    @if($canViewOrders)
        <div class="row">

            {{-- Total Orders --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Orders</p>
                                <h4 class="fw-bold mb-0">
                                    {{ number_format($orderStats['count']) }}
                                </h4>
                            </div>
                            <div class="text-primary fs-3">
                                <i class="bi bi-bag-check"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Total Order Amount --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Order Amount</p>
                                <h4 class="fw-bold mb-0">
                                    ৳{{ number_format($orderStats['amount'], 2) }}
                                </h4>
                            </div>
                            <div class="text-info fs-3">
                                <i class="bi bi-currency-dollar"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Orders --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Pending Orders</p>
                                <h4 class="fw-bold text-warning mb-0">
                                    {{ number_format($orderStats['pending_count']) }}
                                </h4>
                            </div>
                            <div class="text-warning fs-3">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Order Amount --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Pending Order Amount</p>
                                <h4 class="fw-bold text-danger mb-0">
                                    ৳{{ number_format($orderStats['pending_amount'], 2) }}
                                </h4>
                            </div>
                            <div class="text-danger fs-3">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- =====================================================
        INVOICE STATISTICS
    ====================================================== --}}
    @if($canViewInvoices)
        <div class="row">

            {{-- Total Invoice --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Invoice Amount</p>
                                <h4 class="fw-bold mb-0">
                                    ৳{{ number_format($invoiceStats['amount'], 2) }}
                                </h4>
                            </div>
                            <div class="text-primary fs-3">
                                <i class="bi bi-receipt"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Paid Amount --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Paid Amount</p>
                                <h4 class="fw-bold text-success mb-0">
                                    ৳{{ number_format($invoiceStats['paid'], 2) }}
                                </h4>
                            </div>
                            <div class="text-success fs-3">
                                <i class="bi bi-check-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Pending Amount --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Pending Invoice</p>
                                <h4 class="fw-bold text-warning mb-0">
                                    {{$invoiceStats['pending_count'] }}
                                </h4>
                            </div>
                            <div class="text-warning fs-3">
                                <i class="bi bi-hourglass-split"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Due Amount --}}
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="d-flex justify-content-between">
                            <div>
                                <p class="text-muted mb-1">Total Pending Amount</p>
                                <h4 class="fw-bold text-danger mb-0">
                                    ৳{{ number_format($invoiceStats['due'], 2) }}
                                </h4>
                            </div>
                            <div class="text-danger fs-3">
                                <i class="bi bi-exclamation-circle"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    @endif

    {{-- =====================================================
        TRANSACTION STATISTICS
    ====================================================== --}}
    @if ($canViewTransactions)
        <h6 class="text-uppercase text-muted fw-semibold mb-3">
            Transaction Overview
        </h6>

        <div class="row g-3 mb-4">

            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Total Transactions</div>
                        <h3 class="fw-bold mb-0">
                            {{ number_format($transactionStats['count']) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Approved Amount</div>
                        <h3 class="fw-bold text-success mb-0">
                            ৳{{ number_format($transactionStats['paid'], 2) }}
                        </h3>
                    </div>
                </div>
            </div>

            <div class="col-xl-4 col-md-6">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-body">
                        <div class="text-muted small mb-2">Pending Amount</div>
                        <h3 class="fw-bold text-warning mb-0">
                            ৳{{ number_format($transactionStats['pending'], 2) }}
                        </h3>
                    </div>
                </div>
            </div>

        </div>
    @endif


    @if ($canVerifyTransactions && $pendingPaymentRequests > 0)
        <div class="alert alert-warning d-flex flex-wrap justify-content-between align-items-center mb-4">
            <div>
                <i class="bi bi-exclamation-triangle-fill me-2"></i>
                <strong>{{ number_format($pendingPaymentRequests) }}</strong>
                payment transaction(s) are waiting for verification.
            </div>

            <a href="{{ route('admin.payment-transactions.index') }}"
               class="btn btn-sm btn-warning mt-2 mt-md-0">
                Review Payments
                <i class="bi bi-arrow-right ms-1"></i>
            </a>
        </div>
    @endif

    @if($canViewFinancialChart)
        @php
            $growthIndicator = function ($growth, bool $inverse = false) {
                if ($growth === null) {
                    return [
                        'text' => 'NEW',
                        'class' => 'text-secondary',
                        'icon' => '',
                    ];
                }

                $growth = (float) $growth;

                $isPositive = $growth > 0;
                $isNegative = $growth < 0;

                $isGood = $inverse
                    ? $isNegative
                    : $isPositive;

                $class = $growth == 0
                    ? 'text-secondary'
                    : ($isGood ? 'text-success' : 'text-danger');

                $icon = $isPositive
                    ? 'bi-caret-up-fill'
                    : ($isNegative ? 'bi-caret-down-fill' : '');

                $text = ($growth > 0 ? '+' : '')
                    .number_format($growth, 2)
                    .'%';

                return compact('text', 'class', 'icon');
            };

            $revenueGrowth = $growthIndicator(
                $financialSummary['revenue_growth']
            );

            $expenseGrowth = $growthIndicator(
                $financialSummary['expense_growth'],
                true
            );

            $profitGrowth = $growthIndicator(
                $financialSummary['profit_growth']
            );

            $transactionGrowth = $growthIndicator(
                $financialSummary['transaction_growth']
            );
        @endphp



            {{-- Monthly Financial Recap --}}
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

                            {{-- Income & Expense Chart --}}
                            <div class="col-12">
                                <p class="text-center">
                                    <strong>Income vs Expense: January - December {{ now()->year }}</strong>
                                </p>

                                <div style="height: 300px">
                                    <canvas id="sales-chart"
                                            role="img"
                                            aria-label="Monthly income and expense chart"></canvas>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- Financial Summary --}}
                    <div class="card-footer bg-body">
                        <div class="row text-center">

                            {{-- Total Revenue --}}
                            <div class="col-6 col-md-3 mb-3">
                                @php
                                    $revenueGrowth = $financialSummary['revenue_growth'];
                                @endphp

                                @if ($revenueGrowth !== null)
                                    <span class="{{ $revenueGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="bi {{ $revenueGrowth >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                        {{ abs($revenueGrowth) }}%
                                    </span>
                                @endif

                                <h5 class="fw-bold mb-0">
                                    ৳{{ number_format($financialSummary['revenue'], 2) }}
                                </h5>
                                <small>TOTAL REVENUE</small>
                            </div>

                            {{-- Total Cost --}}
                            <div class="col-6 col-md-3 mb-3">
                                @php
                                    $expenseGrowth = $financialSummary['expense_growth'];
                                @endphp

                                @if ($expenseGrowth !== null)
                                    <span class="{{ $expenseGrowth <= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="bi {{ $expenseGrowth >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                        {{ abs($expenseGrowth) }}%
                                    </span>
                                @endif

                                <h5 class="fw-bold mb-0">
                                    ৳{{ number_format($financialSummary['expense'], 2) }}
                                </h5>
                                <small>TOTAL COST</small>
                            </div>

                            {{-- Total Profit --}}
                            <div class="col-6 col-md-3 mb-3">
                                @php
                                    $profitGrowth = $financialSummary['profit_growth'];
                                @endphp

                                @if ($profitGrowth !== null)
                                    <span class="{{ $profitGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="bi {{ $profitGrowth >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                        {{ abs($profitGrowth) }}%
                                    </span>
                                @endif

                                <h5 class="fw-bold mb-0">
                                    ৳{{ number_format($financialSummary['profit'], 2) }}
                                </h5>
                                <small>TOTAL PROFIT</small>
                            </div>

                            {{-- Total Transactions --}}
                            <div class="col-6 col-md-3 mb-3">
                                @php
                                    $transactionGrowth = $financialSummary['transaction_growth'];
                                @endphp

                                @if ($transactionGrowth !== null)
                                    <span class="{{ $transactionGrowth >= 0 ? 'text-success' : 'text-danger' }}">
                                        <i class="bi {{ $transactionGrowth >= 0 ? 'bi-caret-up-fill' : 'bi-caret-down-fill' }}"></i>
                                        {{ abs($transactionGrowth) }}%
                                    </span>
                                @endif

                                <h5 class="fw-bold mb-0">
                                    {{ number_format($financialSummary['transactions']) }}
                                </h5>
                                <small>TOTAL TRANSACTIONS</small>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>
    @endif


    <div class="row">

        {{-- =====================================================
            RECENT INVOICE HISTORY
        ===================================================== --}}
        @if($canViewInvoices)
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0 fw-semibold">
                            Recent Invoice History
                        </h5>
                    </div>

                    <div class="card-body p-0">
                        @if($recentInvoices->isNotEmpty())
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead>
                                        <tr>
                                            <th class="ps-3">Invoice</th>
                                            @if($canViewAllInvoices)
                                                <th>Member</th>
                                            @endif
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Date</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        @foreach($recentInvoices as $invoice)
                                            @php
                                                $status = strtolower($invoice->status ?? '');

                                                $statusClass = match ($status) {
                                                    'paid' => 'success',
                                                    'pending' => 'warning',
                                                    'processing' => 'info',
                                                    'overdue', 'cancelled', 'rejected' => 'danger',
                                                    default => 'secondary',
                                                };
                                            @endphp

                                            <tr>
                                                <td class="ps-3 fw-semibold">
                                                    {{ $invoice->invoice_number ?? '#'.$invoice->id }}
                                                </td>

                                                @if($canViewAllInvoices)
                                                    <td>
                                                        {{ $invoice->user?->full_name ?? 'N/A' }}
                                                    </td>
                                                @endif

                                                <td class="fw-semibold text-nowrap">
                                                    ৳{{ number_format((float) $invoice->amount, 2) }}
                                                </td>

                                                <td>
                                                    <span class="badge bg-{{ $statusClass }}">
                                                        {{ ucfirst($invoice->status ?? 'Unknown') }}
                                                    </span>
                                                </td>

                                                <td class="text-nowrap">
                                                    {{ $invoice->created_at?->format('d M Y') ?? 'N/A' }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @else
                            <div class="text-center py-4">
                                <i class="bi bi-receipt text-muted fs-1"></i>
                                <p class="text-muted mb-0 mt-2">
                                    No invoice history found.
                                </p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        {{-- =====================================================
            RECENT ORDERS
        ===================================================== --}}
        @if($canViewOrders)
            <div class="col-lg-6 mb-4">
                <div class="card border-0 shadow-sm h-100">
                    <div class="card-header border-0 pt-3">
                        <div class="d-flex justify-content-between align-items-center">
                            <h5 class="mb-0 fw-semibold">
                                Recent Orders
                            </h5>

                            @if($canViewAllOrders)
                                <a href="{{ route('orders.index') }}"
                                class="btn btn-sm btn-outline-primary">
                                    View All
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Order</th>
                                        @if($canViewAllOrders)
                                            <th>Member</th>
                                        @endif
                                        <th>Amount</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    @forelse($recentOrders as $order)
                                        @php
                                            $orderStatus = strtolower((string) $order->order_status);

                                            $orderBadge = match ($orderStatus) {
                                                'completed', 'delivered' => 'success',
                                                'pending' => 'warning',
                                                'cancelled', 'rejected' => 'danger',
                                                'processing', 'shipped' => 'info',
                                                default => 'secondary',
                                            };
                                        @endphp

                                        <tr>
                                            <td class="ps-3 fw-semibold">
                                                {{ $order->order_number ?: '#'.$order->id }}
                                            </td>

                                            @if($canViewAllOrders)
                                                <td>
                                                    {{ $order->user?->full_name ?? 'N/A' }}
                                                </td>
                                            @endif

                                            <td class="text-nowrap">
                                                ৳{{ number_format((float) $order->total_amount, 2) }}
                                            </td>

                                            <td>
                                                <span class="badge bg-{{ $orderBadge }}">
                                                    {{ ucfirst(str_replace('_', ' ', $orderStatus ?: 'N/A')) }}
                                                </span>
                                            </td>

                                            <td class="text-nowrap">
                                                {{ $order->created_at?->format('d M Y') ?? 'N/A' }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="{{ $canViewAllOrders ? 5 : 4 }}"
                                                class="text-center text-muted py-4">
                                                No orders found.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- =====================================================
        LATEST TRANSACTIONS
    ====================================================== --}}
    @if ($canViewTransactions)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 pt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Latest Transactions</h5>

                    @if ($canViewAllTransactions)
                        <a href="{{ route('admin.finance.transactions.index') }}"
                           class="btn btn-sm btn-outline-primary">
                            View All
                        </a>
                    @endif
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead >
                            <tr>
                                <th class="ps-3">Transaction ID</th>

                                @if ($canViewAllTransactions)
                                    <th>Member</th>
                                @endif

                                <th>Category</th>
                                <th>Type</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th>Date</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($latestTransactions as $transaction)
                                @php
                                    $transactionStatus = strtolower((string) $transaction->status);

                                    $transactionBadge = match ($transactionStatus) {
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'processing' => 'info',
                                        'rejected', 'cancelled', 'expired' => 'danger',
                                        default => 'secondary',
                                    };

                                    $transactionType = $transaction->category?->type
                                        ?? ($transaction->invoice_item_id ? 'income' : 'expense');

                                    $typeBadge = $transactionType === 'income'
                                        ? 'success'
                                        : 'danger';
                                @endphp

                                <tr>
                                    <td class="ps-3">
                                        <strong>
                                            {{ $transaction->trx_id ?: '#'.$transaction->getKey() }}
                                        </strong>
                                    </td>

                                    @if ($canViewAllTransactions)
                                        <td>
                                            <div class="fw-semibold">
                                                {{ $transaction->user?->full_name ?? 'N/A' }}
                                            </div>

                                            @if ($transaction->user?->membership_number)
                                                <small class="text-muted">
                                                    {{ $transaction->user->membership_number }}
                                                </small>
                                            @endif
                                        </td>
                                    @endif

                                    <td>
                                        {{ $transaction->category?->name
                                            ?? ($transaction->invoice_id ? 'Invoice Payment' : 'Other') }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $typeBadge }}">
                                            {{ ucfirst($transactionType) }}
                                        </span>
                                    </td>

                                    <td class="fw-semibold">
                                        ৳{{ number_format((float) $transaction->amount, 2) }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $transactionBadge }}">
                                            {{ ucfirst($transactionStatus) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $transaction->created_at?->format('d M Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canViewAllTransactions ? 7 : 6 }}"
                                        class="text-center text-muted py-4">
                                        No transactions found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    {{-- =====================================================
        RECENT MEMBERS
    ====================================================== --}}
    {{-- @if ($canViewUsers)
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header border-0 pt-3">
                <div class="d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Recently Added Members</h5>

                    <a href="{{ route('users.index') }}"
                       class="btn btn-sm btn-outline-primary">
                        View All
                    </a>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead >
                            <tr>
                                <th class="ps-3">Member</th>
                                <th>Membership Number</th>
                                <th>Mobile</th>
                                <th>Approval</th>
                                <th>Member Status</th>
                                <th>Joined</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($recentUsers as $member)
                                @php
                                    $approvalBadge = match ($member->approval_status) {
                                        'approved' => 'success',
                                        'pending' => 'warning',
                                        'rejected' => 'danger',
                                        default => 'secondary',
                                    };

                                    $memberBadge = match ($member->member_status) {
                                        'active', 'life_member' => 'success',
                                        'inactive' => 'warning',
                                        'suspended', 'deceased' => 'danger',
                                        'apprentice', 'lawyer_assistant' => 'info',
                                        default => 'secondary',
                                    };
                                @endphp

                                <tr>
                                    <td class="ps-3">
                                        <div class="fw-semibold">
                                            {{ $member->full_name }}
                                        </div>
                                        <small class="text-muted">
                                            {{ $member->email ?? 'No email' }}
                                        </small>
                                    </td>

                                    <td>
                                        {{ $member->membership_number ?? 'N/A' }}
                                    </td>

                                    <td>
                                        {{ $member->mobile ?? 'N/A' }}
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $approvalBadge }}">
                                            {{ ucfirst($member->approval_status ?? 'N/A') }}
                                        </span>
                                    </td>

                                    <td>
                                        <span class="badge bg-{{ $memberBadge }}">
                                            {{ ucfirst(str_replace('_', ' ', $member->member_status ?? 'N/A')) }}
                                        </span>
                                    </td>

                                    <td>
                                        {{ $member->created_at?->format('d M Y') ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-4">
                                        No members found.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif --}}

    {{-- =====================================================
        EMPTY DASHBOARD
    ====================================================== --}}
    @if (
        ! $canViewUsers &&
        ! $canApproveUsers &&
        ! $canChangeMemberStatus &&
        ! $canViewOrders &&
        ! $canViewInvoices &&
        ! $canViewTransactions &&
        ! $canViewFinancialChart &&
        ! $canVerifyTransactions
    )
        <div class="card border-0 shadow-sm">
            <div class="card-body text-center py-5">
                <i class="bi bi-shield-lock text-muted display-4"></i>

                <h5 class="mt-3">Dashboard Access Restricted</h5>

                <p class="text-muted mb-0">
                    You do not currently have permission to view dashboard information.
                    Please contact the system administrator.
                </p>
            </div>
        </div>
    @endif

</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {

    // Monthly Income & Expense Chart
    const salesCanvas = document.getElementById('sales-chart');

    if (salesCanvas) {
        const financialChart = @json($financialChart);

        new Chart(salesCanvas, {
            type: 'line',

            data: {
                labels: financialChart.labels,

                datasets: [
                    {
                        label: 'Income',
                        data: financialChart.income,
                        borderColor: '#20c997',
                        backgroundColor: 'rgba(32, 201, 151, 0.15)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
                    },
                    {
                        label: 'Expense',
                        data: financialChart.expense,
                        borderColor: '#0d6efd',
                        backgroundColor: 'rgba(13, 110, 253, 0.15)',
                        fill: true,
                        tension: 0.4,
                        borderWidth: 2,
                        pointRadius: 3,
                        pointHoverRadius: 5
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

                plugins: {
                    legend: {
                        display: true,
                        position: 'top'
                    },

                    tooltip: {
                        callbacks: {
                            label: function (context) {
                                return context.dataset.label + ': ৳' +
                                    Number(context.parsed.y).toLocaleString('en-BD', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                            }
                        }
                    }
                },

                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function (value) {
                                return '৳' + Number(value).toLocaleString('en-BD');
                            }
                        }
                    }
                }
            }
        });
    }

});
</script>
@endpush