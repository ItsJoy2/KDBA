<?php

namespace App\Services\Finance;

use App\Models\Invoice;
use App\Models\Order;
use App\Models\Transaction;
use App\Models\TransactionCategory;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class FinancialTransactionService
{

    public function categorizeInvoicePayment( Transaction $transaction, Invoice $invoice): void {
        if ($transaction->status !== 'approved') {
            throw new RuntimeException(
                'Only approved payments can be recorded as income.'
            );
        }

        $slug = match ($invoice->invoice_type) {
            'registration' => 'membership-fees',
            'yearly_renewal' => 'yearly-renewal-fees',
            default => null,
        };

        if ($slug === null) {
            throw new RuntimeException(
                'No income category mapping exists for this invoice type.'
            );
        }

        $category = TransactionCategory::query()
            ->where('slug', $slug)
            ->where('type', 'income')
            ->where('is_active', true)
            ->lockForUpdate()
            ->first();

        if (! $category) {
            throw new RuntimeException(
                "Active income category [{$slug}] was not found."
            );
        }

        $transaction->category_id = $category->id;
        $transaction->save();
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

    
}


