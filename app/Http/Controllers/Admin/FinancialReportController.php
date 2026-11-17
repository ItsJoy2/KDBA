<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class FinancialReportController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $dateFrom = $validated['date_from'] ?? now()->startOfMonth()->toDateString();
        $dateTo = $validated['date_to'] ?? now()->toDateString();

        $baseQuery = Transaction::query()
            ->where('transactions.status', 'approved')
            ->whereDate('transactions.created_at', '>=', $dateFrom)
            ->whereDate('transactions.created_at', '<=', $dateTo);

        $income = (clone $baseQuery)
            ->whereHas('category', fn ($q) => $q->where('type', 'income'))
            ->sum('amount');

        $expense = (clone $baseQuery)
            ->whereHas('category', fn ($q) => $q->where('type', 'expense'))
            ->sum('amount');

        $categoryBreakdown = (clone $baseQuery)
            ->join('transaction_categories', 'transaction_categories.id', '=', 'transactions.category_id')
            ->select(
                'transaction_categories.id',
                'transaction_categories.name',
                'transaction_categories.type',
                DB::raw('SUM(transactions.amount) as total_amount'),
                DB::raw('COUNT(transactions.id) as transaction_count')
            )
            ->groupBy(
                'transaction_categories.id',
                'transaction_categories.name',
                'transaction_categories.type'
            )
            ->orderBy('transaction_categories.type')
            ->orderByDesc('total_amount')
            ->get();

        $netBalance = bcsub((string) $income, (string) $expense, 2);

        return view('admin.finance.reports.index', compact(
            'dateFrom',
            'dateTo',
            'income',
            'expense',
            'netBalance',
            'categoryBreakdown'
        ));
    }
}
