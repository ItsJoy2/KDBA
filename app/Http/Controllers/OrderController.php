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
use App\Http\Requests\SubmitOrderPaymentRequest;
use App\Models\PaymentGateway;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Services\Finance\FinancialTransactionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Database\QueryException;
use Throwable;

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
                fn ($query) => $query->where('user_id', $user->id)
            )
            ->when(
                $request->filled('status'),
                fn ($query) => $query->where(
                    'order_status',
                    $request->input('status')
                )
            )
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($subQuery) use ($search) {
                    $subQuery->where(
                        'order_number',
                        'like',
                        "%{$search}%"
                    )->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where(
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

        // Active payment gateways for Order Payment Modal
        $paymentGateways = \App\Models\PaymentGateway::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'account_number',
                'type',
            ]);

        return view('orders.index', compact(
            'orders',
            'canViewAll',
            'paymentGateways'
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
        

    public function submitPayment(
        SubmitOrderPaymentRequest $request,
        Order $order
    ): RedirectResponse {
        $validated = $request->validated();

        $receiptPath = null;

        try {
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')
                    ->store('transactions/receipts', 'public');
            }

            DB::transaction(function () use (
                $validated,
                $order,
                $request,
                &$receiptPath
            ): void {
                $lockedOrder = Order::query()
                    ->whereKey($order->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ((int) $lockedOrder->user_id !== (int) auth()->id()) {
                    abort(403);
                }

                if ($lockedOrder->payment_status === 'paid') {
                    throw ValidationException::withMessages([
                        'order' => 'This order has already been paid.',
                    ]);
                }

                if ($lockedOrder->order_status === 'cancelled') {
                    throw ValidationException::withMessages([
                        'order' => 'Payment is not allowed for a cancelled order.',
                    ]);
                }

                $existingPayment = Transaction::query()
                    ->where('order_id', $lockedOrder->id)
                    ->whereIn('status', ['pending', 'approved'])
                    ->exists();

                if ($existingPayment) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'A payment is already submitted for this order.',
                    ]);
                }

                $gateway = PaymentGateway::query()
                    ->whereKey($validated['payment_gateway_id'])
                    ->where('is_active', true)
                    ->firstOrFail();

                $category = TransactionCategory::query()
                    ->where('slug', 'product-sales')
                    ->where('type', 'income')
                    ->where('is_active', true)
                    ->first();

                if (! $category) {
                    throw ValidationException::withMessages([
                        'payment_gateway_id' => 'Product Sales income category is not configured.',
                    ]);
                }

                $isCash = strtolower($gateway->type ?? '') === 'cash';

                Transaction::create([
                    'order_id' => $lockedOrder->id,
                    'invoice_id' => null,
                    'invoice_item_id' => null,

                    'user_id' => $lockedOrder->user_id,
                    'category_id' => $category->id,
                    'payment_gateway_id' => $gateway->id,

                    'trx_id' => $isCash
                        ? 'CASH-' . Str::upper(Str::random(8))
                        : $validated['trx_id'],

                    'sender_mobile' => $isCash
                        ? 'N/A'
                        : $validated['sender_mobile'],

                    'amount' => $lockedOrder->total_amount,
                    'status' => 'pending',
                    'source' => 'member',

                    'recorded_by' => auth()->id(),
                    'verified_by' => null,
                    'verified_at' => null,

                    'rejection_reason' => null,
                    'receipt_path' => $receiptPath,
                    'submitted_at' => now(),
                ]);

                $lockedOrder->update([
                    'payment_status' => 'pending',
                ]);
            }, 3);

            app(FinancialTransactionService::class)
                ->clearFinancialCache();

            return redirect()
                ->route('orders.index')
                ->with('success', 'Order payment submitted successfully. Awaiting admin verification.');

        } catch (Throwable $exception) {
            if ($receiptPath) {
                Storage::disk('public')->delete($receiptPath);
            }

            throw $exception;
        }
    }

}