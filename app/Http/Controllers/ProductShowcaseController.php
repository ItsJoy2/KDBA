<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductShowcaseController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        $products = Product::query()
            ->where('status', 'active')
            ->where('stock_quantity', '>', 0)
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $canOrder = $user
            && in_array($user->member_status, [
                'member',
                'life_member',
            ], true)
            && $user->hasPermission('products.buy');

        return view('shop.index', compact('products', 'canOrder'));
    }

    public function orderHistory(Request $request): View
    {
        $user = $request->user();

        $orders = Order::query()
            ->with([
                'items.product',
                'purchaser:id,full_name',
            ])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('shop.orders', compact('orders'));
    }
}
