<?php

namespace App\Http\Controllers\Member;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    public function index(Request $request): View
    {
        $transactions = Transaction::query()
            ->where('user_id', auth()->id())
            ->with([
                'category:id,name,type',
                'invoice:id,invoice_number,invoice_type',
                'paymentGateway:id,name',
            ])
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->status);
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

        return view('member.transactions.index', compact('transactions'));
    }
}