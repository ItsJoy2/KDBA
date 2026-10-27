<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Http\Requests\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('orders.view'),
            403
        );

        $user = $request->user();
        $canViewAll = $user->hasPermission('orders.view_all');

        $orders = Order::query()
            ->with([
                'user:id,full_name,mobile',
                'purchaser:id,full_name',
            ])
            ->when(
                ! $canViewAll,
                fn ($q) => $q->where('user_id', $user->id)
            )
            ->when(
                $request->filled('status'),
                fn ($q) => $q->where(
                    'order_status',
                    $request->input('status')
                )
            )
            ->when($request->filled('search'), function ($q) use ($request) {
                $search = trim($request->input('search'));

                $q->where(function ($sub) use ($search) {
                    $sub->where(
                        'order_number',
                        'like',
                        "%{$search}%"
                    )->orWhereHas('user', function ($u) use ($search) {
                        $u->where(
                            'full_name',
                            'like',
                            "%{$search}%"
                        );
                    });
                });
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('orders.index', compact(
            'orders',
            'canViewAll'
        ));
    }

    public function create(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('products.buy'),
            403
        );

        $products = Product::active()
            ->where('stock_quantity', '>', 0)
            ->orderBy('name')
            ->get();

        $canOrderForOthers = $request->user()
            ->hasPermission('products.buy_for_others');

        $users = $canOrderForOthers
            ? User::query()
                ->orderBy('full_name')
                ->get(['id', 'full_name', 'mobile'])
            : collect();

        $itemErrors = collect(session('errors')?->getBag('default')->getMessages() ?? [])
        ->filter(fn ($messages, $key) => str_starts_with($key, 'items'))
        ->all();

    // Validation fail er por selected member restore korar jonno
    $selectedMember = old('user_id')
        ? User::find(old('user_id'), [
            'id', 'full_name', 'mobile', 'email', 'membership_number',
        ])
        : null;

        return view('orders.create', compact('products','users','canOrderForOthers','itemErrors', 'selectedMember'));
    }

    public function store(
        StoreOrderRequest $request,
        OrderService $orderService
    ): RedirectResponse {
        $data = $request->validated();

        $order = $orderService->create(
            $request->user(),
            $data['items'],
            isset($data['user_id']) && $data['user_id'] !== ''
                ? (int) $data['user_id']
                : null
        );

        return redirect()
            ->route('orders.index', $order)
            ->with('success', 'Order created successfully.');
    }

    public function show(Request $request, Order $order): View
    {
        $user = $request->user();

        $isOwnOrder = (int) $order->user_id === (int) $user->id;

        $canViewOwnOrders = $user->hasPermission('orders.view_own');

        $canViewAllOrders = $user->hasPermission('orders.view_all');

        abort_unless(
            ($isOwnOrder && $canViewOwnOrders) || $canViewAllOrders,
            403,
            'You are not authorized to view this order.'
        );

        $order->load([
            'user',
            'purchaser',
            'items.product',
            'serials.product',
        ]);

        return view('orders.show', compact('order'));
    }

    public function updateStatus(
        UpdateOrderStatusRequest $request,
        Order $order,
        OrderService $orderService
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasPermission('orders.status_update'),
            403
        );

        $orderService->updateStatus(
            $order->id,
            $request->validated()['order_status']
        );

        return back()->with(
            'success',
            'Order status updated successfully.'
        );
    }

    public function searchSuggestions(Request $request): JsonResponse{
        $user = $request->user();
        $type = $request->query('type', 'member');
        $search = trim((string) $request->query('q', ''));

        if ($type === 'member') {
            abort_unless(
                $user->hasPermission('products.buy_for_others'),
                403
            );

            if (mb_strlen($search) < 2) {
                return response()->json([]);
            }

            return response()->json(
                User::query()
                    ->whereIn('status', ['Active', 'Life Member'])
                    ->where(function ($query) use ($search) {
                        $query->where('full_name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%")
                            ->orWhere('membership_number', 'like', "%{$search}%");
                    })
                    ->orderBy('full_name')
                    ->limit(10)
                    ->get([
                        'id',
                        'full_name',
                        'mobile',
                        'email',
                        'membership_number',
                    ])
            );
        }

        if ($type === 'product') {
            abort_unless($user->hasPermission('products.buy'), 403);

            // Restore previously selected products after validation failure.
            $ids = $request->query('ids', []);

            if (is_array($ids) && count($ids) > 0) {
                $ids = collect($ids)
                    ->filter(fn ($id) => filter_var($id, FILTER_VALIDATE_INT) !== false)
                    ->unique()
                    ->values()
                    ->all();

                return response()->json(
                    Product::query()
                        ->where('status', 'active')
                        ->whereIn('id', $ids)
                        ->get([
                            'id',
                            'name',
                            'selling_price',
                            'stock_quantity',
                            'track_serials',
                            'serial_start',
                            'serial_end',
                            'current_serial',
                        ])
                );
            }

            if (mb_strlen($search) < 2) {
                return response()->json([]);
            }

            return response()->json(
                Product::query()
                    ->where('status', 'active')
                    ->where('stock_quantity', '>', 0)
                    ->where('name', 'like', "%{$search}%")
                    ->orderBy('name')
                    ->limit(10)
                    ->get([
                        'id',
                        'name',
                        'selling_price',
                        'stock_quantity',
                        'track_serials',
                        'serial_start',
                        'serial_end',
                        'current_serial',
                    ])
            );
        }

        return response()->json([
            'message' => 'Invalid search type.',
        ], 422);
    }
        
}