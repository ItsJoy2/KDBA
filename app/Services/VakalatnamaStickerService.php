<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\VakalatnamaSticker;
use App\Models\VakalatnamaStickerOrder;
use App\Support\Amount;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class VakalatnamaStickerService
{
    public function createOrder(
        User $actor,
        int $memberId,
        int $quantity
    ): VakalatnamaStickerOrder {
        if ($quantity < 1 || $quantity > 500) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be between 1 and 500.',
            ]);
        }

        $canCreateForOthers = $actor->hasPermission(
            'vakalatnama-sticker-create'
        );

        $canCreateOwn = $actor->hasPermission(
            'vakalatnama-sticker-create-own'
        );

        if (! $canCreateForOthers && ! $canCreateOwn) {
            abort(403);
        }

        if (! $canCreateForOthers && (int) $actor->id !== $memberId) {
            abort(403, 'You can only create your own sticker order.');
        }

        $enabled = SystemSetting::getValue(
            'vakalatnama_sticker_enabled',
            true
        );

        if (! filter_var($enabled, FILTER_VALIDATE_BOOLEAN)) {
            throw ValidationException::withMessages([
                'quantity' => 'Sticker service is currently disabled.',
            ]);
        }

        $member = User::query()
            ->whereKey($memberId)
            ->where('approval_status', 'approved')
            ->whereIn('member_status', ['active', 'life_member'])
            ->first();

        if (! $member) {
            throw ValidationException::withMessages([
                'member_id' => 'Selected member is not eligible.',
            ]);
        }

        $unitPrice = (string) SystemSetting::getValue(
            'vakalatnama_sticker_price',
            '100.00'
        );

        $unitPaisa = $this->toPaisa($unitPrice);

        if ($unitPaisa < 1 || $unitPaisa > 100000000) {
            throw ValidationException::withMessages([
                'quantity' => 'Sticker price is not configured correctly.',
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $member,
            $quantity,
            $unitPaisa
        ) {
            $totalPaisa = $unitPaisa * $quantity;

            $order = VakalatnamaStickerOrder::create([
                'user_id' => $member->id,
                'created_by' => $actor->id,
                'order_number' => $this->generateOrderNumber(),
                'quantity' => $quantity,
                'unit_price' => $this->fromPaisa($unitPaisa),
                'total_amount' => $this->fromPaisa($totalPaisa),
                'invoice_id' => null,
                'status' => 'pending',
            ]);

            $now = now();
            $stickers = [];

            for ($i = 0; $i < $quantity; $i++) {
                $stickers[] = [
                    'order_id' => $order->id,
                    'user_id' => $member->id,
                    'unique_number' => $this->generateUniqueNumber(),
                    'status' => 'pending',
                    'print_count' => 0,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            VakalatnamaSticker::insert($stickers);

            return $order->load([
                'user:id,full_name,mobile,membership_number',
                'creator:id,full_name',
                'stickers:id,order_id,status',
            ]);
        }, 3);
    }

    public function submitPayment(
        User $actor,
        VakalatnamaStickerOrder $order,
        array $data,
        ?string $receiptPath = null
    ): Transaction {
        return DB::transaction(function () use (
            $actor,
            $order,
            $data,
            $receiptPath
        ) {
            $lockedOrder = VakalatnamaStickerOrder::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ((int) $lockedOrder->user_id !== (int) $actor->id) {
                abort(403, 'You cannot pay for this order.');
            }

            if (! in_array($lockedOrder->status, ['pending'], true)) {
                throw ValidationException::withMessages([
                    'order' => 'This order is not available for payment.',
                ]);
            }

            $existingPayment = Transaction::query()
                ->where('vakalatnama_sticker_order_id', $lockedOrder->id)
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($existingPayment) {
                throw ValidationException::withMessages([
                    'order' => 'A payment has already been submitted for this order.',
                ]);
            }

            $gateway = PaymentGateway::query()
                ->whereKey($data['payment_gateway_id'])
                ->where('is_active', true)
                ->first();

            if (! $gateway) {
                throw ValidationException::withMessages([
                    'payment_gateway_id' => 'Selected payment gateway is unavailable.',
                ]);
            }

            $isCash = strtolower((string) $gateway->type) === 'cash';

            if (! $isCash && Transaction::query()
                ->where('trx_id', $data['trx_id'])
                ->exists()) {
                throw ValidationException::withMessages([
                    'trx_id' => 'This transaction ID has already been used.',
                ]);
            }

            $category = TransactionCategory::query()
                ->where('slug', 'vakalatnama-sticker-sales')
                ->where('type', 'income')
                ->where('is_active', true)
                ->first();

            if (! $category) {
                throw new \RuntimeException(
                    'Vakalatnama Sticker income category is not configured.'
                );
            }

            $now = now();

            $transaction = Transaction::create([
                'vakalatnama_sticker_order_id' => $lockedOrder->id,
                'invoice_id' => null,
                'order_id' => null,
                'invoice_item_id' => null,
                'order_item_id' => null,

                'user_id' => $lockedOrder->user_id,
                'category_id' => $category->id,
                'payment_gateway_id' => $gateway->id,

                'trx_id' => $isCash
                    ? 'CASH-' . Str::upper(Str::random(12))
                    : trim($data['trx_id']),

                'sender_mobile' => $isCash
                    ? 'N/A'
                    : trim($data['sender_mobile']),

                'amount' => $lockedOrder->total_amount,
                'description' => 'Vakalatnama Sticker Order: '
                    . $lockedOrder->order_number,

                'status' => 'pending',
                'source' => 'member',
                'recorded_by' => $actor->id,
                'verified_by' => null,
                'verified_at' => null,
                'rejection_reason' => null,
                'receipt_path' => $receiptPath,
                'submitted_at' => $now,
            ]);

            $lockedOrder->update([
                'status' => 'processing',
            ]);

            return $transaction;
        }, 3);
    }

    protected function generateOrderNumber(): string
    {
        return 'VKO-' . now()->format('Ymd') . '-'
            . Str::upper(Str::random(10));
    }

    protected function generateUniqueNumber(): string
    {
        return 'VKS-' . now()->format('Y') . '-'
            . Str::upper(Str::random(16));
    }

    private function toPaisa(string $amount): int
    {
        $amount = trim($amount);

        if (! preg_match('/^\d{1,8}(?:\.\d{1,2})?$/', $amount)) {
            return 0;
        }

        [$whole, $fraction] = array_pad(
            explode('.', $amount, 2),
            2,
            '0'
        );

        return ((int) $whole * 100)
            + (int) str_pad($fraction, 2, '0');
    }

    private function fromPaisa(int $paisa): string
    {
        return intdiv($paisa, 100)
            . '.'
            . str_pad((string) ($paisa % 100), 2, '0');
    }
}
