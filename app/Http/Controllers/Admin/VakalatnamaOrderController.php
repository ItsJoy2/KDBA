<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Vakalatnama\StoreVakalatnamaStickerOrderRequest;
use App\Http\Requests\Vakalatnama\StoreVakalatnamaStickerPaymentRequest;
use App\Models\PaymentGateway;
use App\Models\User;
use App\Models\VakalatnamaStickerOrder;
use App\Services\VakalatnamaStickerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Throwable;

class VakalatnamaOrderController extends Controller
{
    public function __construct(
        protected VakalatnamaStickerService $stickerService
    ) {
    }

    /**
     * Admin: List all Vakalatnama sticker orders.
     */
    public function adminIndex(Request $request): View
    {
        abort_unless(
            $request->user()->can('vakalatnama.view'),
            403
        );

        $query = VakalatnamaStickerOrder::query()
            ->with([
                'user:id,name,membership_number,phone',
                'creator:id,name',
                'approver:id,name',
                'transaction',
            ])
            ->withCount('stickers')
            ->latest();

        // Restrict staff without view-all access to their own
        // created orders. Grant broader scope through the
        // existing RBAC permission.
        if (! $request->user()->can('vakalatnama.view_all')) {
            $query->where('created_by', $request->user()->id);
        }

        if ($request->filled('search')) {
            $search = trim($request->string('search')->toString());

            $query->where(function ($builder) use ($search) {
                $builder->where('order_number', 'like', "%{$search}%")
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('membership_number', 'like', "%{$search}%");
                    });
            });
        }

        if ($request->filled('payment_status')) {
            $query->where(
                'payment_status',
                $request->input('payment_status')
            );
        }

        $orders = $query->paginate(20)->withQueryString();

        return view(
            'admin.vakalatnama-stickers.index',
            compact('orders')
        );
    }

    /**
     * Admin: Show order creation page.
     */
    public function adminCreate(Request $request): View
    {
        abort_unless(
            $request->user()->can('vakalatnama.create'),
            403
        );

        $members = User::query()
            ->where('approval_status', 'approved')
            ->where('member_status', 'member')
            ->orderBy('name')
            ->get([
                'id',
                'name',
                'membership_number',
                'phone',
            ]);

        $unitPrice = $this->stickerService->getUnitPrice();

        return view(
            'admin.vakalatnama-stickers.create',
            compact('members', 'unitPrice')
        );
    }

    /**
     * Admin: Create order for a selected member.
     */
    public function adminStore(
        StoreVakalatnamaStickerOrderRequest $request
    ): RedirectResponse {
        abort_unless(
            $request->user()->can('vakalatnama.create'),
            403
        );

        $order = $this->stickerService->createOrder(
            actor: $request->user(),
            memberId: (int) $request->validated('user_id'),
            quantity: (int) $request->validated('quantity')
        );

        return redirect()
            ->route('admin.vakalatnama.show', $order)
            ->with(
                'success',
                'Vakalatnama sticker order created successfully.'
            );
    }

    /**
     * Admin: View a specific order.
     */
    public function adminShow(
        Request $request,
        VakalatnamaStickerOrder $order
    ): View {
        abort_unless(
            $request->user()->can('vakalatnama.view'),
            403
        );

        if (! $request->user()->can('vakalatnama.view_all')) {
            abort_unless(
                (int) $order->created_by === (int) $request->user()->id,
                403
            );
        }

        $order->load([
            'user',
            'creator',
            'approver',
            'transaction.paymentGateway',
            'stickers',
        ]);

        return view(
            'admin.vakalatnama-stickers.show',
            compact('order')
        );
    }

    /**
     * Member: List only their own orders.
     */
    public function memberIndex(Request $request): View
    {
        $user = $request->user();

        $orders = VakalatnamaStickerOrder::query()
            ->where('user_id', $user->id)
            ->withCount('stickers')
            ->latest()
            ->paginate(15);

        return view(
            'member.vakalatnama-stickers.index',
            compact('orders')
        );
    }

    /**
     * Member: Show order creation page.
     */
    public function memberCreate(Request $request): View
    {
        abort_unless(
            $this->stickerService->isEnabled(),
            403,
            'Vakalatnama sticker ordering is currently disabled.'
        );

        $unitPrice = $this->stickerService->getUnitPrice();

        return view(
            'member.vakalatnama-stickers.create',
            compact('unitPrice')
        );
    }

    /**
     * Member: Create an order for themselves.
     */
    public function memberStore(
        StoreVakalatnamaStickerOrderRequest $request
    ): RedirectResponse {
        $order = $this->stickerService->createOrder(
            actor: $request->user(),
            memberId: $request->user()->id,
            quantity: (int) $request->validated('quantity')
        );

        return redirect()
            ->route('member.vakalatnama.show', $order)
            ->with(
                'success',
                'Your Vakalatnama sticker order was created successfully.'
            );
    }

    /**
     * Member: View their own order.
     */
    public function memberShow(
        Request $request,
        VakalatnamaStickerOrder $order
    ): View {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            403
        );

        // Never expose unique_number in member-facing views.
        $order->load([
            'stickers:id,order_id,user_id,record_number,status,print_count,printed_at',
            'transaction.paymentGateway',
        ]);

        return view(
            'member.vakalatnama-stickers.show',
            compact('order')
        );
    }

    /**
     * Member: Submit payment.
     *
     * The receipt is stored on a private disk so that it
     * cannot be accessed through a public URL.
     */
    public function memberPay(
        StoreVakalatnamaStickerPaymentRequest $request,
        VakalatnamaStickerOrder $order
    ): RedirectResponse {
        abort_unless(
            (int) $order->user_id === (int) $request->user()->id,
            403
        );

        $receiptPath = null;

        try {
            if ($request->hasFile('receipt')) {
                $receiptPath = $request->file('receipt')
                    ->store('vakalatnama/receipts', 'local');
            }

            $this->stickerService->recordPayment(
                actor: $request->user(),
                orderId: $order->id,
                gatewayId: (int) $request->validated('payment_gateway_id'),
                trxId: $request->validated('trx_id'),
                senderMobile: $request->validated('sender_mobile'),
                receiptPath: $receiptPath,
                remarks: $request->validated('remarks')
            );
        } catch (Throwable $exception) {
            if ($receiptPath) {
                Storage::disk('local')->delete($receiptPath);
            }

            throw $exception;
        }

        return redirect()
            ->route('member.vakalatnama.show', $order)
            ->with(
                'success',
                'Payment submitted successfully. Please wait for verification.'
            );
    }

    /**
     * Admin: Record payment on behalf of a member.
     *
     * Useful for cash counter payments and office collections.
     * The payment still goes through the existing verification
     * process.
     */
    public function adminPay(
        StoreVakalatnamaStickerPaymentRequest $request,
        VakalatnamaStickerOrder $order
    ): RedirectResponse {
        abort_unless(
            $request->user()->can('vakalatnama.pay'),
            403
        );

        $this->stickerService->recordPayment(
            actor: $request->user(),
            orderId: $order->id,
            gatewayId: (int) $request->validated('payment_gateway_id'),
            trxId: $request->validated('trx_id'),
            senderMobile: $request->validated('sender_mobile'),
            remarks: $request->validated('remarks')
        );

        return redirect()
            ->route('admin.vakalatnama.show', $order)
            ->with(
                'success',
                'Payment recorded successfully and submitted for verification.'
            );
    }

    /**
     * Load available payment gateways for payment pages.
     */
    public function paymentGateways(): \Illuminate\Database\Eloquent\Collection
    {
        return PaymentGateway::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get([
                'id',
                'name',
                'type',
                'account_number',
                'instruction',
            ]);
    }
}
