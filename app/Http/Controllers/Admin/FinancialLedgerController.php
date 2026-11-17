<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FinancialLedgerController extends Controller
{
    public function index(Request $request): View
    {
        $validated = $request->validate([
            'type' => ['nullable', 'in:income,expense'],
            'status' => ['nullable', 'in:pending,approved,rejected'],
            'category_id' => ['nullable', 'integer', 'exists:transaction_categories,id'],
            'date_from' => ['nullable', 'date'],
            'date_to' => ['nullable', 'date', 'after_or_equal:date_from'],
        ]);

        $transactions = Transaction::query()
            ->with([
                'user:id,full_name,membership_number',
                'category:id,name,type',
                'invoice:id,invoice_number,invoice_type',
                'paymentGateway:id,name',
            ])
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
            })
            ->when($request->filled('type'), function ($query) use ($request) {
                $query->whereHas('category', function ($categoryQuery) use ($request) {
                    $categoryQuery->where('type', $request->type);
                });
            })
            ->when($request->filled('from'), function ($query) use ($request) {
                $query->whereDate('created_at', '>=', $request->from);
            })
            ->when($request->filled('to'), function ($query) use ($request) {
                $query->whereDate('created_at', '<=', $request->to);
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        $categories = TransactionCategory::query()
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('admin.finance.transactions.index', compact(
            'transactions',
            'categories',
            'validated'
        ));
    }
}
