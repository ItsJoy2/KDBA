<?php

namespace App\Services;

use App\Models\PaymentGateway;
use App\Models\SystemSetting;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Models\User;
use App\Models\VakalatnamaSticker;
use App\Models\VakalatnamaStickerOrder;
use App\Models\VakalatnamaStickerPrintLog;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class VakalatnamaStickerService
{
    private const PRICE_KEY = 'vakalatnama_sticker_price';

    private const ENABLED_KEY = 'vakalatnama_sticker_enabled';

    private const CACHE_PREFIX = 'system_setting:';

    private const MAX_QUANTITY = 500;

    private const UNIQUE_NUMBER_ATTEMPTS = 20;

    /**
     * Get the current sticker unit price.
     *
     * The price is cached to reduce repeated database queries.
     */
    public function getUnitPrice(): float
    {
        return (float) Cache::remember(
            self::CACHE_PREFIX . self::PRICE_KEY,
            now()->addHours(24),
            fn () => SystemSetting::getValue(
                self::PRICE_KEY,
                '100.00'
            )
        );
    }

    /**
     * Determine whether sticker ordering is enabled.
     */
    public function isEnabled(): bool
    {
        return (bool) Cache::remember(
            self::CACHE_PREFIX . self::ENABLED_KEY,
            now()->addHours(24),
            fn () => SystemSetting::getValue(
                self::ENABLED_KEY,
                '1'
            )
        );
    }

    /**
     * Return current sticker settings.
     */
    public function getSettings(): array
    {
        return [
            'price' => $this->getUnitPrice(),
            'enabled' => $this->isEnabled(),
        ];
    }

    /**
     * Update sticker settings.
     *
     * Existing orders are not affected because each order
     * stores its own unit price and total amount.
     */
    public function updateSettings(
        User $actor,
        float $price,
        bool $enabled
    ): void {
        if (
            ! $actor->can('vakalatnama.settings')
            && ! $actor->can('settings.manage')
        ) {
            throw new RuntimeException(
                'You are not authorized to update Vakalatnama settings.'
            );
        }

        if (! is_finite($price) || $price <= 0) {
            throw ValidationException::withMessages([
                'price' => 'Sticker price must be greater than zero.',
            ]);
        }

        DB::transaction(function () use ($price, $enabled) {
            SystemSetting::setValue(
                self::PRICE_KEY,
                number_format($price, 2, '.', ''),
                'text',
                'vakalatnama_sticker'
            );

            SystemSetting::setValue(
                self::ENABLED_KEY,
                $enabled ? '1' : '0',
                'boolean',
                'vakalatnama_sticker'
            );
        });

        $this->clearSettingsCache();
    }

    /**
     * Clear sticker settings cache.
     */
    public function clearSettingsCache(): void
    {
        Cache::forget(
            self::CACHE_PREFIX . self::PRICE_KEY
        );

        Cache::forget(
            self::CACHE_PREFIX . self::ENABLED_KEY
        );
    }

    /**
     * Validate member eligibility.
     */
    public function validateEligibleMember(User $member): void
    {
        $eligible = $member->approval_status === 'approved'
            && in_array(
                $member->member_status,
                ['active', 'member'],
                true
            );

        if (! $eligible) {
            throw ValidationException::withMessages([
                'user_id' => 'This user is not eligible to order Vakalatnama stickers.',
            ]);
        }
    }

    /**
     * Create a Vakalatnama sticker order.
     *
     * Every requested sticker is created individually.
     *
     * IMPORTANT:
     * No unique sticker number is generated here.
     *
     * @return VakalatnamaStickerOrder
     */
    public function createOrder(
        User $actor,
        int $memberId,
        int $quantity
    ): VakalatnamaStickerOrder {
        if (! $this->isEnabled()) {
            throw ValidationException::withMessages([
                'order' => 'Vakalatnama sticker ordering is currently disabled.',
            ]);
        }

        if ($quantity < 1 || $quantity > self::MAX_QUANTITY) {
            throw ValidationException::withMessages([
                'quantity' => 'Quantity must be between 1 and 500.',
            ]);
        }

        if (
            $memberId !== (int) $actor->id
            && ! $actor->can('vakalatnama.create')
        ) {
            throw ValidationException::withMessages([
                'user_id' => 'You cannot create orders for other members.',
            ]);
        }

        return DB::transaction(function () use (
            $actor,
            $memberId,
            $quantity
        ) {
            $member = User::query()
                ->lockForUpdate()
                ->findOrFail($memberId);

            $this->validateEligibleMember($member);

            // Read the current price inside the order creation
            // operation and preserve it permanently on the order.
            $unitPrice = $this->getUnitPrice();

            $totalAmount = round(
                $unitPrice * $quantity,
                2
            );

            $order = VakalatnamaStickerOrder::create([
                'user_id' => $member->id,
                'created_by' => $actor->id,
                'order_number' => $this->generateOrderNumber(),
                'quantity' => $quantity,
                'unit_price' => number_format(
                    $unitPrice,
                    2,
                    '.',
                    ''
                ),
                'total_amount' => number_format(
                    $totalAmount,
                    2,
                    '.',
                    ''
                ),
                'payment_status' => 'pending',
                'status' => 'pending',
            ]);

            // Create individual sticker records.
            // Sticker numbers remain NULL until payment approval.
            $stickers = [];

            for ($i = 0; $i < $quantity; $i++) {
                $stickers[] = [
                    'order_id' => $order->id,
                    'user_id' => $member->id,
                    'record_number' => $this->generateRecordNumber(),
                    'unique_number' => null,
                    'status' => 'pending',
                    'print_count' => 0,
                    'printed_at' => null,
                    'printed_by' => null,
                    'last_printed_at' => null,
                    'last_printed_by' => null,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }

            foreach (array_chunk($stickers, 100) as $chunk) {
                VakalatnamaSticker::insert($chunk);
            }

            return $order->load([
                'user',
                'stickers',
            ]);
        }, 3);
    }

    /**
     * Generate a unique order number.
     */
    private function generateOrderNumber(): string
    {
        do {
            $number = 'KDBA-VS-' . now()->format('Ymd')
                . '-'
                . strtoupper(Str::random(8));

        } while (
            VakalatnamaStickerOrder::where(
                'order_number',
                $number
            )->exists()
        );

        return $number;
    }

    /**
     * Generate a unique human-readable sticker record number.
     */
    private function generateRecordNumber(): string
    {
        do {
            $number = 'VS-' . now()->format('ymd')
                . '-'
                . strtoupper(Str::random(10));

        } while (
            VakalatnamaSticker::where(
                'record_number',
                $number
            )->exists()
        );

        return $number;
    }


    public function recordPayment(
        User $actor,
        int $orderId,
        int $gatewayId,
        ?string $trxId = null,
        ?string $senderMobile = null,
        ?string $receiptPath = null,
        ?string $remarks = null
    ): Transaction {
        return DB::transaction(function () use (
            $actor,
            $orderId,
            $gatewayId,
            $trxId,
            $senderMobile,
            $receiptPath,
            $remarks
        ) {
            $order = VakalatnamaStickerOrder::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            if (
                (int) $actor->id !== (int) $order->user_id
                && ! $actor->can('vakalatnama.pay')
            ) {
                throw ValidationException::withMessages([
                    'payment' => 'You are not authorized to record payment for this order.',
                ]);
            }

            $this->authorizeOrderAccess($actor, $order);

            if (! in_array(
                $order->payment_status,
                ['pending', 'rejected'],
                true
            )) {
                throw ValidationException::withMessages([
                    'payment' => 'This order cannot accept another payment request.',
                ]);
            }

            $gateway = PaymentGateway::query()
                ->whereKey($gatewayId)
                ->where('is_active', true)
                ->first();

            if (! $gateway) {
                throw ValidationException::withMessages([
                    'payment_gateway_id' => 'The selected payment gateway is unavailable.',
                ]);
            }

            if ($gateway->type === 'cash') {
                $trxId = $this->generateCashTransactionNumber();
                $senderMobile = null;
            } else {
                if (! $trxId || ! $senderMobile) {
                    throw ValidationException::withMessages([
                        'payment' => 'Transaction ID and sender mobile are required.',
                    ]);
                }
            }

            // Prevent reuse of an external transaction ID.
            if (Transaction::where('trx_id', $trxId)->exists()) {
                throw ValidationException::withMessages([
                    'trx_id' => 'This transaction ID has already been submitted.',
                ]);
            }

            // Prevent multiple pending payment requests for the same order.
            $existingPayment = Transaction::query()
                ->where('id', $order->transaction_id)
                ->where('status', 'pending')
                ->exists();

            if ($existingPayment) {
                throw ValidationException::withMessages([
                    'payment' => 'A payment request is already awaiting verification.',
                ]);
            }

            $category = TransactionCategory::query()
                ->where('slug', 'vakalatnama-sticker')
                ->where('type', 'income')
                ->where('is_active', true)
                ->first();

            if (! $category || ! $category->is_system) {
                throw new RuntimeException(
                    'The protected Vakalatnama income category is missing or invalid.'
                );
            }

            $transaction = Transaction::create([
                'user_id' => $order->user_id,
                'category_id' => $category->id,
                'payment_gateway_id' => $gateway->id,
                'trx_id' => $trxId,
                'sender_mobile' => $senderMobile,
                'amount' => $order->total_amount,
                'status' => 'pending',
                'source' => 'vakalatnama_sticker',
                'recorded_by' => $actor->id,
                'receipt_path' => $receiptPath,
                'submitted_at' => now(),
            ]);

            $order->update([
                'transaction_id' => $transaction->id,
                'payment_status' => 'processing',
                'status' => 'pending',
            ]);

            return $transaction->load([
                'user',
                'paymentGateway',
                'category',
            ]);
        }, 3);
    }

    private function generateCashTransactionNumber(): string
    {
        do {
            $number = 'CASH-' . strtoupper(Str::random(12));

        } while (
            Transaction::where('trx_id', $number)->exists()
        );

        return $number;
    }

    /**
     * Authorize access to an order.
     */
    public function authorizeOrderAccess(
        User $actor,
        VakalatnamaStickerOrder $order
    ): void {
        if ((int) $actor->id === (int) $order->user_id) {
            return;
        }

        if (
            ! $actor->can('vakalatnama.view')
            && ! $actor->can('transactions.view_all')
        ) {
            throw ValidationException::withMessages([
                'order' => 'You are not authorized to access this order.',
            ]);
        }
    }


    public function completePayment(
        User $approver,
        int $transactionId
    ): VakalatnamaStickerOrder {
        return DB::transaction(function () use (
            $approver,
            $transactionId
        ) {
            $transaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transactionId);

            if (
                ! $approver->can('transactions.verify')
                && ! $approver->can('vakalatnama.verify')
            ) {
                throw ValidationException::withMessages([
                    'permission' => 'You are not authorized to approve sticker payments.',
                ]);
            }

            if ($transaction->status !== 'approved') {
                throw ValidationException::withMessages([
                    'transaction' => 'The transaction must be approved before issuing stickers.',
                ]);
            }

            $category = TransactionCategory::query()
                ->find($transaction->category_id);

            if (
                ! $category
                || $category->slug !== 'vakalatnama-sticker'
                || $category->type !== 'income'
                || ! $category->is_system
            ) {
                throw ValidationException::withMessages([
                    'transaction' => 'This is not a valid Vakalatnama sticker payment.',
                ]);
            }

            $order = VakalatnamaStickerOrder::query()
                ->where('transaction_id', $transaction->id)
                ->lockForUpdate()
                ->first();

            if (! $order) {
                throw new ModelNotFoundException(
                    'The Vakalatnama sticker order was not found.'
                );
            }

            if ($order->payment_status === 'paid') {
                return $order->load('stickers');
            }

            if (
                (int) $transaction->user_id !== (int) $order->user_id
                || (float) $transaction->amount !== (float) $order->total_amount
            ) {
                throw ValidationException::withMessages([
                    'transaction' => 'Payment details do not match the sticker order.',
                ]);
            }

            $order->update([
                'payment_status' => 'paid',
                'status' => 'paid',
                'approved_by' => $approver->id,
                'approved_at' => now(),
                'rejection_reason' => null,
            ]);

            // Generate individual numbers only after payment approval.
            $stickers = VakalatnamaSticker::query()
                ->where('order_id', $order->id)
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($stickers->count() !== $order->quantity) {
                throw new RuntimeException(
                    'The sticker quantity does not match the order quantity.'
                );
            }

            foreach ($stickers as $sticker) {
                if ($sticker->unique_number !== null) {
                    continue;
                }

                $this->assignUniqueNumber($sticker);
            }

            return $order->fresh([
                'user',
                'transaction',
                'stickers',
            ]);
        }, 3);
    }


    public function rejectPayment(
        User $approver,
        int $transactionId,
        string $reason
    ): VakalatnamaStickerOrder {
        return DB::transaction(function () use (
            $approver,
            $transactionId,
            $reason
        ) {
            $transaction = Transaction::query()
                ->lockForUpdate()
                ->findOrFail($transactionId);

            if ($transaction->status !== 'rejected') {
                throw ValidationException::withMessages([
                    'transaction' => 'The transaction must be rejected first.',
                ]);
            }

            $order = VakalatnamaStickerOrder::query()
                ->where('transaction_id', $transaction->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($order->payment_status === 'paid') {
                throw ValidationException::withMessages([
                    'payment' => 'A paid order cannot be rejected.',
                ]);
            }

            $order->update([
                'payment_status' => 'rejected',
                'status' => 'pending',
                'approved_by' => null,
                'approved_at' => null,
                'rejection_reason' => $reason,
            ]);

            return $order->fresh();
        }, 3);
    }


    private function generateUniqueNumber(): string
    {
        for (
            $attempt = 1;
            $attempt <= self::UNIQUE_NUMBER_ATTEMPTS;
            $attempt++
        ) {
            $number = (string) random_int(1, 9);

            for ($i = 1; $i < 18; $i++) {
                $number .= (string) random_int(0, 9);
            }

            $exists = VakalatnamaSticker::query()
                ->where('unique_number', $number)
                ->exists();

            if (! $exists) {
                return $number;
            }
        }

        throw new RuntimeException(
            'Unable to generate a unique sticker number after multiple attempts.'
        );
    }

    /**
     * Atomically claim a sticker for printing.
     *
     * First print:
     * - Requires paid order.
     * - Requires issued sticker.
     * - Requires print permission.
     * - Sets print_count to 1.
     *
     * Reprint:
     * - Requires explicit reprint permission.
     * - Increments print_count.
     * - Preserves first-print information.
     *
     * @return VakalatnamaSticker
     */
    public function claimPrint(
        User $actor,
        int $stickerId,
        bool $isReprint = false,
        ?string $remarks = null
    ): VakalatnamaSticker {
        return DB::transaction(function () use (
            $actor,
            $stickerId,
            $isReprint,
            $remarks
        ) {
            $sticker = VakalatnamaSticker::query()
                ->lockForUpdate()
                ->findOrFail($stickerId);

            $order = VakalatnamaStickerOrder::query()
                ->lockForUpdate()
                ->findOrFail($sticker->order_id);

            if (
                $order->payment_status !== 'paid'
                || $order->status !== 'paid'
            ) {
                throw ValidationException::withMessages([
                    'sticker' => 'Unpaid stickers cannot be printed.',
                ]);
            }

            if (
                $sticker->status !== 'issued'
                || ! $sticker->unique_number
            ) {
                throw ValidationException::withMessages([
                    'sticker' => 'This sticker has not been issued or has been revoked.',
                ]);
            }

            if ($isReprint) {
                if (! $actor->can('vakalatnama.reprint')) {
                    throw ValidationException::withMessages([
                        'permission' => 'You are not authorized to reprint stickers.',
                    ]);
                }

                if ($sticker->print_count < 1) {
                    throw ValidationException::withMessages([
                        'sticker' => 'The sticker has not been printed before.',
                    ]);
                }
            } else {
                if (! $actor->can('vakalatnama.print')) {
                    throw ValidationException::withMessages([
                        'permission' => 'You are not authorized to print stickers.',
                    ]);
                }

                if ($sticker->print_count !== 0) {
                    throw ValidationException::withMessages([
                        'sticker' => 'This sticker has already been printed. Reprint authorization is required.',
                    ]);
                }
            }

            $now = now();

            $update = [
                'print_count' => $sticker->print_count + 1,
                'last_printed_at' => $now,
                'last_printed_by' => $actor->id,
            ];

            if ($sticker->print_count === 0) {
                $update['printed_at'] = $now;
                $update['printed_by'] = $actor->id;
            }

            $sticker->update($update);

            VakalatnamaStickerPrintLog::create([
                'sticker_id' => $sticker->id,
                'printed_by' => $actor->id,
                'print_type' => $isReprint
                    ? 'reprint'
                    : 'first_print',
                'printed_at' => $now,
                'remarks' => $remarks,
            ]);

            return $sticker->fresh([
                'order.user',
                'user',
                'printer',
                'lastPrinter',
            ]);
        }, 3);
    }

    /**
     * Get sticker data for an authorized printing operation.
     *
     * This method explicitly retrieves the hidden unique number.
     */
    public function getStickerForPrinting(
        User $actor,
        int $stickerId
    ): VakalatnamaSticker {
        $sticker = VakalatnamaSticker::query()
            ->with([
                'user',
                'order',
            ])
            ->findOrFail($stickerId);

        if (! $actor->can('vakalatnama.print')
            && ! $actor->can('vakalatnama.reprint')) {
            throw ValidationException::withMessages([
                'permission' => 'You are not authorized to access sticker printing.',
            ]);
        }

        if (
            $sticker->status !== 'issued'
            || ! $sticker->unique_number
            || ! $sticker->order
            || $sticker->order->payment_status !== 'paid'
        ) {
            throw ValidationException::withMessages([
                'sticker' => 'This sticker is not available for printing.',
            ]);
        }

        return $sticker;
    }

    /**
     * Return sticker verification information.
     *
     * This method is intended for the public verification
     * controller and deliberately excludes private data.
     */
    public function verifySticker(
        string $uniqueNumber
    ): ?array {
        if (! preg_match('/^[0-9]{18}$/', $uniqueNumber)) {
            return null;
        }

        $sticker = VakalatnamaSticker::query()
            ->with([
                'user',
                'order',
            ])
            ->where('unique_number', $uniqueNumber)
            ->where('status', 'issued')
            ->first();

        if (! $sticker || ! $sticker->order) {
            return null;
        }

        $order = $sticker->order;

        if ($order->payment_status !== 'paid') {
            return null;
        }

        return [
            'record_number' => $sticker->record_number,
            'member_name' => $sticker->user->name,
            'membership_number' => $sticker->user->membership_number
                ?? null,
            'unit_price' => $order->unit_price,
            'status' => $sticker->status,
            'is_printed' => $sticker->print_count > 0,
            'printed_at' => $sticker->printed_at,
            'issued_at' => $order->approved_at,
            'valid' => true,
        ];
    }


    private function assignUniqueNumber(
        VakalatnamaSticker $sticker
    ): void {
        for (
            $attempt = 1;
            $attempt <= self::UNIQUE_NUMBER_ATTEMPTS;
            $attempt++
        ) {
            $number = (string) random_int(1, 9);

            for ($i = 1; $i < 18; $i++) {
                $number .= (string) random_int(0, 9);
            }

            try {
                $sticker->forceFill([
                    'unique_number' => $number,
                    'status' => 'issued',
                ])->save();

                return;
            } catch (\Illuminate\Database\QueryException $exception) {
                // MySQL duplicate key error.
                $errorCode = $exception->errorInfo[1] ?? null;

                if (
                    (string) $errorCode !== '1062'
                    && (string) $errorCode !== '1555'
                    && (string) $errorCode !== '2067'
                ) {
                    throw $exception;
                }
            }
        }

        throw new RuntimeException(
            'Unable to assign a unique sticker number after multiple attempts.'
        );
    }


}
