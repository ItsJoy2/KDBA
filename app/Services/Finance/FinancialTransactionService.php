<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use App\Support\Amount;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use RuntimeException;

class FinancialTransactionService
{

    public function categorizeInvoicePayment(Transaction $transaction): void
    {
        $lockedTransaction = Transaction::query()
            ->whereKey($transaction->id)
            ->lockForUpdate()
            ->firstOrFail();

        if (
            !$lockedTransaction->invoice_id ||
            $lockedTransaction->invoice_item_id !== null
        ) {
            throw ValidationException::withMessages([
                'transaction' => 'Invalid invoice payment transaction.',
            ]);
        }

        if ($lockedTransaction->status !== 'approved') {
            throw ValidationException::withMessages([
                'transaction' => 'Only approved invoice payments can be recorded as income.',
            ]);
        }

        $category = TransactionCategory::query()
            ->where('slug', 'invoice')
            ->where('type', 'income')
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (!$category) {
            throw ValidationException::withMessages([
                'category' => 'The active Invoice income category was not found. Please seed the default category.',
            ]);
        }

        $lockedTransaction->update([
            'category_id' => $category->id,
        ]);
    }

    /**
     * Clear standardized financial summary/report cache keys.
     */
    public function clearFinancialCache(): void
    {
        DB::afterCommit(function () {
            foreach ([
                'finance.dashboard.summary',
                'finance.reports.income-expense',
                'finance.reports.monthly',
            ] as $key) {
                Cache::forget($key);
            }
        });
    }

    public function recordShopOrder(Order $order): bool
    {
        $recorded = DB::transaction(function () use ($order): bool {
            $lockedOrder = Order::query()
                ->whereKey($order->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Do not create accounting entries for unpaid orders.
            if ($lockedOrder->payment_status !== 'paid') {
                return false;
            }

            $lockedOrder->load(['items.product']);

            if ($lockedOrder->items->isEmpty()) {
                throw new RuntimeException(
                    "Order {$lockedOrder->order_number} has no items."
                );
            }

            // The purchaser is the payer; fall back to the order owner.
            $accountUserId = $lockedOrder->purchased_by
                ?: $lockedOrder->user_id;

            if (! $accountUserId) {
                throw new RuntimeException(
                    "Order {$lockedOrder->order_number} has no associated user."
                );
            }

            $salesAmount = '0.00';
            $costAmount = '0.00';

            foreach ($lockedOrder->items as $item) {
                $quantity = (int) $item->quantity;

                if ($quantity <= 0) {
                    throw new RuntimeException(
                        "Invalid quantity in order {$lockedOrder->order_number}."
                    );
                }

                if (! is_numeric($item->price) || bccomp((string) $item->price, '0', 2) < 0) {
                    throw new RuntimeException(
                        "Invalid item selling price in order {$lockedOrder->order_number}."
                    );
                }

                if (! $item->product) {
                    throw new RuntimeException(
                        "Product not found for order item {$item->id}."
                    );
                }

                $lineSales = bcmul(
                    (string) $item->price,
                    (string) $quantity,
                    2
                );

                $salesAmount = bcadd($salesAmount, $lineSales, 2);

                // Current product buy_price is used because OrderItem
                // currently does not store a historical cost snapshot.
                $buyPrice = $item->product->buy_price;

                if ($buyPrice === null || ! is_numeric($buyPrice)) {
                    throw new RuntimeException(
                        "Product cost is missing for product ID {$item->product_id}."
                    );
                }

                if (bccomp((string) $buyPrice, '0', 2) < 0) {
                    throw new RuntimeException(
                        "Invalid product cost for product ID {$item->product_id}."
                    );
                }

                $lineCost = bcmul(
                    (string) $buyPrice,
                    (string) $quantity,
                    2
                );

                $costAmount = bcadd($costAmount, $lineCost, 2);
            }

            if (bccomp($salesAmount, '0.00', 2) <= 0) {
                throw new RuntimeException(
                    "Order {$lockedOrder->order_number} has an invalid sales total."
                );
            }

            $incomeCategory = $this->getActiveCategory(
                'product-sales',
                'income'
            );

            $expenseCategory = $this->getActiveCategory(
                'product-cost-cogs',
                'expense'
            );

            $reference = $lockedOrder->order_number;

            // One sales income entry per order.
            $this->createShopAccountingEntry(
                "SHOP-{$reference}-INCOME",
                $incomeCategory,
                $accountUserId,
                $salesAmount,
                "Product sales income for order {$reference}."
            );

            // One aggregated COGS expense entry per order.
            if (bccomp($costAmount, '0.00', 2) > 0) {
                $this->createShopAccountingEntry(
                    "SHOP-{$reference}-COGS",
                    $expenseCategory,
                    $accountUserId,
                    $costAmount,
                    "Product cost (COGS) for order {$reference}."
                );
            }

            return true;
        });

        if ($recorded) {
            $this->clearFinancialCache();
        }

        return $recorded;
    }

    private function getActiveCategory(
        string $slug,
        string $type
    ): TransactionCategory {
        $category = TransactionCategory::query()
            ->where('slug', $slug)
            ->where('type', $type)
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (! $category) {
            throw new RuntimeException(
                "Active transaction category [{$slug}] was not found."
            );
        }

        return $category;
    }

    private function createShopAccountingEntry(
        string $reference,
        TransactionCategory $category,
        int $userId,
        string $amount,
        string $description
    ): void {
        // Prevent duplicate accounting entries for the same order.
        $exists = Transaction::query()
            ->where('trx_id', $reference)
            ->lockForUpdate()
            ->exists();

        if ($exists) {
            return;
        }

        Transaction::create([
            'user_id' => $userId,
            'invoice_id' => null,
            'payment_gateway_id' => null,
            'category_id' => $category->id,
            'trx_id' => $reference,
            'sender_mobile' => null,
            'amount' => $amount,
            'description' => $description,
            'status' => 'approved',
            'source' => 'admin',
            'recorded_by' => null,
            'verified_by' => null,
            'verified_at' => now(),
            'submitted_at' => now(),
        ]);
    }


    public function recordInvoiceItems(
        Invoice $invoice,
        ?int $recordedBy,
        string $baseTrxId,
        ?int $paymentGatewayId
    ): void {
        $invoice->loadMissing('items');

        if (blank($baseTrxId)) {
            throw ValidationException::withMessages([
                'transaction' => 'Original payment transaction ID is missing.',
            ]);
        }

        foreach ($invoice->items->sortBy('id')->values() as $index => $item) {

            $category = TransactionCategory::query()
                ->where('slug', $item->category_slug)
                ->where('type', 'income')
                ->firstOrFail();

            $itemTrxId = $baseTrxId . '-' . str_pad(
                (string) ($index + 1),
                2,
                '0',
                STR_PAD_LEFT
            );

            // Prevent duplicate accounting entries.
            $existingTransaction = Transaction::query()
                ->where('invoice_item_id', $item->id)
                ->exists();

            if ($existingTransaction) {
                throw ValidationException::withMessages([
                    'transaction' => "Accounting transaction already exists for invoice item {$item->id}.",
                ]);
            }

            Transaction::create([
                'invoice_id' => $invoice->id,
                'invoice_item_id' => $item->id,
                'user_id' => $invoice->user_id,
                'category_id' => $category->id,
                'payment_gateway_id' => $paymentGatewayId,
                'trx_id' => $itemTrxId,
                'amount' => $item->amount,
                'status' => 'approved',
                'source' => 'system',
                'recorded_by' => $recordedBy,
                'verified_by' => $recordedBy,
                'verified_at' => now(),
            ]);
        }
    }

        
    public function createInvoicePaymentTransactions(Invoice $invoice, string $paymentReference, int $gatewayId, string $senderMobile, string $status, int $recordedBy, ?string $receiptPath = null): void
    {
        if (! in_array($status, ['pending', 'approved'], true)) {
            throw new RuntimeException('Invalid invoice payment status.');
        }

        DB::transaction(function () use (
            $invoice,
            $paymentReference,
            $gatewayId,
            $senderMobile,
            $status,
            $recordedBy,
            $receiptPath
        ): void {
            $lockedInvoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (! in_array($lockedInvoice->status, ['pending', 'overdue'], true)) {
                throw new RuntimeException('Invoice is not available for payment.');
            }

            $items = $lockedInvoice->items()
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            if ($items->isEmpty()) {
                throw new RuntimeException('Invoice has no items.');
            }

            // Validate item total against invoice total.
            $itemsTotal = '0.00';

            foreach ($items as $item) {
                if (
                    ! is_numeric($item->amount)
                    || bccomp((string) $item->amount, '0.00', 2) <= 0
                ) {
                    throw new RuntimeException(
                        "Invalid amount for invoice item [{$item->id}]."
                    );
                }

                $itemsTotal = bcadd(
                    $itemsTotal,
                    (string) $item->amount,
                    2
                );
            }

            if (! Amount::equals($itemsTotal, $lockedInvoice->amount)) {
                throw new RuntimeException(
                    'Invoice item total does not match invoice amount.'
                );
            }

            // Do not allow another active payment attempt.
            $existingPayment = Transaction::query()
                ->where('invoice_id', $lockedInvoice->id)
                ->whereNotNull('invoice_item_id')
                ->whereIn('status', ['pending', 'approved'])
                ->exists();

            if ($existingPayment) {
                throw new RuntimeException(
                    'An active payment already exists for this invoice.'
                );
            }

            // Generate one timestamp for the entire payment attempt.
            $submittedAt = now();

            foreach ($items as $index => $item) {
                $categorySlug = match ($item->fee_key) {
                    'admission_fee' => 'membership-fees',
                    'monthly_membership' => 'monthly-fees',
                    'chamber_rent' => 'chamber-rent',

                    'annual_building_fund',
                    'annual_library_fee',
                    'annual_library_card_fee',
                    'annual_relief_fund',
                    'annual_education_fund',
                    'annual_retirement_fund',
                    'annual_mb_fund' => 'yearly-fees',

                    default => throw new RuntimeException(
                        "No category mapping exists for invoice item [{$item->fee_key}]."
                    ),
                };

                $category = $this->getActiveCategory(
                    $categorySlug,
                    'income'
                );

                $itemReference = sprintf(
                    '%s-%02d',
                    $paymentReference,
                    $index + 1
                );

                Transaction::query()->create([
                    'invoice_item_id' => $item->id,
                    'invoice_id' => $lockedInvoice->id,
                    'user_id' => $lockedInvoice->user_id,
                    'payment_gateway_id' => $gatewayId,
                    'category_id' => $category->id,
                    'trx_id' => $itemReference,
                    'sender_mobile' => $senderMobile,
                    'amount' => $item->amount,
                    'status' => $status,
                    'source' => 'admin',
                    'recorded_by' => $recordedBy,
                    'verified_by' => $status === 'approved' ? $recordedBy : null,
                    'verified_at' => $status === 'approved' ? now() : null,
                    'receipt_path' => $receiptPath,
                    'submitted_at' => $submittedAt,
                ]);
            }

            $lockedInvoice->update([
                'status' => $status === 'approved' ? 'paid' : 'processing',
                'paid_at' => $status === 'approved' ? now() : null,
            ]);
        }, 3);

        $this->clearFinancialCache();
    }


    
}


