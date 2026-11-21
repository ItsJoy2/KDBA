<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        /*
        |--------------------------------------------------------------------------
        | Permissions
        |--------------------------------------------------------------------------
        */

        $canViewUsers = $user->hasPermission('users.view');
        $canApproveUsers = $user->hasPermission('users.approve');
        $canChangeMemberStatus = $user->hasPermission('users.change_status');

        $canViewAllOrders = $user->hasPermission('orders.view_all')
            || $user->hasPermission('orders.view');

        $canViewOwnOrders = $user->hasPermission('orders.view_own');
        $canViewOrders = $canViewAllOrders || $canViewOwnOrders;

        $canViewAllInvoices = $user->hasPermission('invoices.view_all');
        $canViewOwnInvoices = $user->hasPermission('invoices.view_own')
            || $user->hasPermission('invoices.view');

        $canViewInvoices = $canViewAllInvoices || $canViewOwnInvoices;

        $canViewAllTransactions = $user->hasPermission('transactions.view_all')
            || $user->hasPermission('finance.view');

        $canViewOwnTransactions = $user->hasPermission('transactions.view_own')
            || $user->hasPermission('transactions.view');

        $canViewTransactions = $canViewAllTransactions || $canViewOwnTransactions;

        $canVerifyTransactions = $user->hasPermission('transactions.verify');

        $canViewFinancialChart = $user->hasPermission('finance.view')
            || $user->hasPermission('transactions.view_all');

        /*
        |--------------------------------------------------------------------------
        | Default values
        |--------------------------------------------------------------------------
        */

        $totalUsers = 0;
        $activeUsers = 0;
        $pendingUsers = 0;
        $inactiveUsers = 0;

        $recentUsers = collect();

        $orderStats = [
            'count' => 0,
            'amount' => 0.00,
        ];

        $recentOrders = collect();

        $invoiceStats = [
            'count' => 0,
            'amount' => 0.00,
            'paid' => 0.00,
            'due' => 0.00,
            'pending_count' => 0,
            'pending_amount' => 0.00,
        ];

        $transactionStats = [
            'count' => 0,
            'paid' => 0.00,
            'pending' => 0.00,
        ];

        $latestTransactions = collect();
        $pendingPaymentRequests = 0;

        $financialChartMonthly = [
            'labels' => [],
            'income' => [],
            'expense' => [],
        ];

        $financialChartYearly = [
            'labels' => [],
            'income' => [],
            'expense' => [],
        ];

        $financialSummary = [
            'income' => 0.00,
            'expense' => 0.00,
        ];

        /*
        |--------------------------------------------------------------------------
        | Membership statistics
        |--------------------------------------------------------------------------
        */

        if ($canViewUsers || $canApproveUsers || $canChangeMemberStatus) {
            $membersQuery = User::query()
                ->whereDoesntHave('roles', function (Builder $query) {
                    $query->where('slug', 'super-admin');
                });

            $totalUsers = (clone $membersQuery)->count();

            $activeUsers = (clone $membersQuery)
                ->where('approval_status', 'approved')
                ->whereIn('member_status', ['active', 'life_member'])
                ->count();

            $pendingUsers = (clone $membersQuery)
                ->where('approval_status', 'pending')
                ->count();

            $inactiveUsers = (clone $membersQuery)
                ->whereIn('member_status', ['inactive', 'suspended'])
                ->count();

            if ($canViewUsers) {
                $recentUsers = (clone $membersQuery)
                    ->select([
                        'id',
                        'full_name',
                        'email',
                        'mobile',
                        'membership_number',
                        'approval_status',
                        'member_status',
                        'created_at',
                    ])
                    ->latest()
                    ->limit(8)
                    ->get();
            }
        }

        // =========================================================
        // ORDER STATISTICS
        // =========================================================

        $canViewAllOrders = $user->hasPermission('orders.view_all')
            || $user->hasPermission('orders.view');

        $canViewOwnOrders = $user->hasPermission('orders.view_own');

        $canViewOrders = $canViewAllOrders || $canViewOwnOrders;

        $orderStats = [
            'count' => 0,
            'amount' => 0,
            'pending_count' => 0,
            'pending_amount' => 0,
        ];

        $recentOrders = collect();

        if ($canViewOrders) {
            $ordersQuery = Order::query();

            // Restrict members to their own orders.
            if (! $canViewAllOrders) {
                $ordersQuery->where('user_id', $user->id);
            }

            $orderStats = [
                'count' => (clone $ordersQuery)->count(),

                'amount' => (float) (clone $ordersQuery)
                    ->sum('total_amount'),

                'pending_count' => (clone $ordersQuery)
                    ->where('order_status', 'pending')
                    ->count(),

                'pending_amount' => (float) (clone $ordersQuery)
                    ->where('order_status', 'pending')
                    ->sum('total_amount'),
            ];

            // Fetch latest 8 orders for dashboard.
            $recentOrdersQuery = Order::query()
                ->with('user:id,full_name,membership_number')
                ->select([
                    'id',
                    'user_id',
                    'order_number',
                    'total_amount',
                    'order_status',
                    'created_at',
                ])
                ->latest('created_at');

            if (! $canViewAllOrders) {
                $recentOrdersQuery->where('user_id', $user->id);
            }

            $recentOrders = $recentOrdersQuery
                ->limit(8)
                ->get();
        }

        // =========================================================
        // INVOICE STATISTICS
        // =========================================================

        $canViewAllInvoices = $user->hasPermission('invoices.view_all');

        $canViewOwnInvoices = $user->hasPermission('invoices.view_own')
            || $user->hasPermission('invoices.view');

        $canViewInvoices = $canViewAllInvoices || $canViewOwnInvoices;

        $invoiceStats = [
            'count' => 0,
            'amount' => 0,
            'paid' => 0,
            'due' => 0,
            'pending_count' => 0,
            'pending_amount' => 0,
        ];

        if ($canViewInvoices) {
            $invoicesQuery = Invoice::query();

            // Member can only see their own invoices.
            if (! $canViewAllInvoices) {
                $invoicesQuery->where('user_id', $user->id);
            }

            $invoiceStats = [
                'count' => (clone $invoicesQuery)->count(),

                // Total invoice amount
                'amount' => (float) (clone $invoicesQuery)->sum('amount'),

                // Paid invoice amount
                'paid' => (float) (clone $invoicesQuery)
                    ->where('status', 'paid')
                    ->sum('amount'),

                // Pending / Processing / Overdue invoice amount
                'due' => (float) (clone $invoicesQuery)
                    ->whereIn('status', ['pending', 'processing', 'overdue'])
                    ->sum('amount'),

                'pending_count' => (clone $invoicesQuery)
                    ->whereIn('status', ['pending', 'processing', 'overdue'])
                    ->count(),

                'pending_amount' => (float) (clone $invoicesQuery)
                    ->whereIn('status', ['pending', 'processing', 'overdue'])
                    ->sum('amount'),
            ];
        }

        // =========================================================
        // RECENT INVOICE HISTORY
        // =========================================================

        $recentInvoices = collect();

        if ($canViewInvoices) {
            $recentInvoicesQuery = Invoice::query()
                ->select([
                    'id',
                    'invoice_number',
                    'user_id',
                    'amount',
                    'status',
                    'created_at',
                ])
                ->latest();

            // Members can only see their own invoices.
            if (! $canViewAllInvoices) {
                $recentInvoicesQuery->where('user_id', $user->id);
            } else {
                $recentInvoicesQuery->with('user:id,full_name,membership_number');
            }

            $recentInvoices = $recentInvoicesQuery
                ->limit(8)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Transaction statistics and latest transactions
        |--------------------------------------------------------------------------
        */

        if ($canViewTransactions) {
            $transactionsQuery = Transaction::query();

            if (! $canViewAllTransactions) {
                $transactionsQuery->where('user_id', $user->id);
            }

            $transactionStats = [
                'count' => (clone $transactionsQuery)->count(),

                'paid' => (float) (clone $transactionsQuery)
                    ->where('status', 'approved')
                    ->sum('amount'),

                'pending' => (float) (clone $transactionsQuery)
                    ->whereIn('status', ['pending', 'processing'])
                    ->sum('amount'),
            ];

            $latestTransactionsQuery = Transaction::query()
                ->with([
                    'user:id,full_name,membership_number',
                    'category:id,name,type',
                    'paymentGateway:id,name',
                    'invoice:id,invoice_number',
                    'order:id,order_number',
                ])
                ->select([
                    'id',
                    'invoice_id',
                    'order_id',
                    'invoice_item_id',
                    'user_id',
                    'category_id',
                    'payment_gateway_id',
                    'trx_id',
                    'amount',
                    'status',
                    'created_at',
                ])
                ->latest();

            if (! $canViewAllTransactions) {
                $latestTransactionsQuery->where('user_id', $user->id);
            }

            $latestTransactions = $latestTransactionsQuery
                ->limit(6)
                ->get();
        }

        /*
        |--------------------------------------------------------------------------
        | Pending payment verification requests
        |--------------------------------------------------------------------------
        */

        if ($canVerifyTransactions) {
            $pendingPaymentRequests = Transaction::query()
                ->where('status', 'pending')
                ->count();
        }

        // =========================================================
        // FINANCIAL RECAP REPORT
        // =========================================================

        $financialChart = [
            'labels' => [],
            'income' => [],
            'expense' => [],
        ];

        $financialSummary = [
            'revenue' => 0,
            'expense' => 0,
            'profit' => 0,
            'transactions' => 0,
            'revenue_growth' => null,
            'expense_growth' => null,
            'profit_growth' => null,
            'transaction_growth' => null,
        ];

        $canViewFinancialChart = $user->hasPermission('finance.view')
            || $user->hasPermission('transactions.view_all')
            || $user->hasPermission('transactions.view_own')
            || $user->hasPermission('transactions.view');

        if ($canViewFinancialChart) {
            $currentYear = now()->year;
            $previousYear = $currentYear - 1;

            /*
            |--------------------------------------------------------------------------
            | Base Query
            |--------------------------------------------------------------------------
            | Only approved transactions with income/expense categories.
            */

            $buildFinancialQuery = function (int $year) use (
                $user,
                $canViewAllTransactions
            ) {
                $query = DB::table('transactions')
                    ->join(
                        'transaction_categories',
                        'transaction_categories.id',
                        '=',
                        'transactions.category_id'
                    )
                    ->where('transactions.status', 'approved')
                    ->whereIn('transaction_categories.type', ['income', 'expense'])
                    ->whereYear('transactions.created_at', $year);

                // Members can only see their own transactions.
                if (! $canViewAllTransactions) {
                    $query->where('transactions.user_id', $user->id);
                }

                return $query;
            };

            /*
            |--------------------------------------------------------------------------
            | Monthly Chart Data
            |--------------------------------------------------------------------------
            */

            $monthlyTransactions = $buildFinancialQuery($currentYear)
                ->selectRaw('
                    MONTH(transactions.created_at) as month,
                    transaction_categories.type as type,
                    SUM(transactions.amount) as total
                ')
                ->groupBy('month', 'transaction_categories.type')
                ->get();

            $monthlyIncome = array_fill(1, 12, 0);
            $monthlyExpense = array_fill(1, 12, 0);

            foreach ($monthlyTransactions as $transaction) {
                $month = (int) $transaction->month;

                if ($transaction->type === 'income') {
                    $monthlyIncome[$month] = (float) $transaction->total;
                } elseif ($transaction->type === 'expense') {
                    $monthlyExpense[$month] = (float) $transaction->total;
                }
            }

            $financialChart = [
                'labels' => [
                    'January', 'February', 'March', 'April',
                    'May', 'June', 'July', 'August',
                    'September', 'October', 'November', 'December',
                ],
                'income' => array_values($monthlyIncome),
                'expense' => array_values($monthlyExpense),
            ];

            /*
            |--------------------------------------------------------------------------
            | Current Year Summary
            |--------------------------------------------------------------------------
            */

            $currentSummary = $buildFinancialQuery($currentYear)
                ->selectRaw('
                    transaction_categories.type as type,
                    SUM(transactions.amount) as total,
                    COUNT(transactions.id) as transaction_count
                ')
                ->groupBy('transaction_categories.type')
                ->get()
                ->keyBy('type');

            $revenue = (float) ($currentSummary['income']->total ?? 0);
            $expense = (float) ($currentSummary['expense']->total ?? 0);

            $transactionCount = (int) (
                $currentSummary->sum('transaction_count')
            );

            /*
            |--------------------------------------------------------------------------
            | Previous Year Summary
            |--------------------------------------------------------------------------
            */

            $previousSummary = $buildFinancialQuery($previousYear)
                ->selectRaw('
                    transaction_categories.type as type,
                    SUM(transactions.amount) as total,
                    COUNT(transactions.id) as transaction_count
                ')
                ->groupBy('transaction_categories.type')
                ->get()
                ->keyBy('type');

            $previousRevenue = (float) ($previousSummary['income']->total ?? 0);
            $previousExpense = (float) ($previousSummary['expense']->total ?? 0);

            $previousProfit = $previousRevenue - $previousExpense;

            $previousTransactionCount = (int) (
                $previousSummary->sum('transaction_count')
            );

            $profit = $revenue - $expense;

            /*
            |--------------------------------------------------------------------------
            | Percentage Growth
            |--------------------------------------------------------------------------
            */

            $calculateGrowth = function (float $current, float $previous): ?float {
                if ($previous == 0.0) {
                    return $current == 0.0 ? 0.0 : null;
                }

                return round((($current - $previous) / abs($previous)) * 100, 2);
            };

            $financialSummary = [
                'revenue' => $revenue,
                'expense' => $expense,
                'profit' => $profit,
                'transactions' => $transactionCount,

                'revenue_growth' => $calculateGrowth(
                    $revenue,
                    $previousRevenue
                ),

                'expense_growth' => $calculateGrowth(
                    $expense,
                    $previousExpense
                ),

                'profit_growth' => $calculateGrowth(
                    $profit,
                    $previousProfit
                ),

                'transaction_growth' => $calculateGrowth(
                    (float) $transactionCount,
                    (float) $previousTransactionCount
                ),
            ];
        }

        return view('dashboard.index', compact(
            'canViewUsers',
            'canApproveUsers',
            'canChangeMemberStatus',

            'canViewAllOrders',
            'canViewOwnOrders',
            'canViewOrders',

            'canViewAllInvoices',
            'canViewOwnInvoices',
            'canViewInvoices',

            'canViewAllTransactions',
            'canViewOwnTransactions',
            'canViewTransactions',
            'canVerifyTransactions',

            'canViewFinancialChart',

            'totalUsers',
            'activeUsers',
            'pendingUsers',
            'inactiveUsers',
            'recentUsers',

            'orderStats',
            'recentOrders',

            'invoiceStats',
            'recentInvoices',

            'transactionStats',
            'latestTransactions',
            'pendingPaymentRequests',

            'financialChart',
            'financialSummary'
        ));
    }
}