<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function create(User $actor, array $items, ?int $targetUserId = null): Order
    {
        try {
            return $this->createOrder($actor, $items, $targetUserId);
        } catch (QueryException $e) {
            // Race condition: unique index (product_id, serial_number) fail korle
            if ((string) $e->getCode() === '23000') {
                throw ValidationException::withMessages([
                    'items' => 'One or more serial numbers were just sold. Please use different serial numbers.',
                ]);
            }

            throw $e;
        }
    }

    private function createOrder(User $actor, array $items, ?int $targetUserId): Order
    {
        return DB::transaction(function () use ($actor, $items, $targetUserId) {
            $canOrderForOthers = $actor->hasPermission('products.buy_for_others');

            if ($canOrderForOthers) {
                $target = $targetUserId
                    ? User::query()->findOrFail($targetUserId)
                    : $this->superAdmin();
            } else {
                // Ignore any target user ID submitted by an ordinary member.
                $target = $actor;
            }

            // product_id => [key, quantity, serials]
            $lines = [];

            foreach ($items as $key => $item) {
                $quantity = (int) $item['quantity'];

                if ($quantity === 0) {
                    continue;
                }

                $productId = (int) $item['product_id'];

                if (isset($lines[$productId])) {
                    throw ValidationException::withMessages([
                        'items' => 'Do not submit the same product more than once.',
                    ]);
                }

                $lines[$productId] = [
                    'key' => $key,
                    'quantity' => $quantity,
                    'serials' => array_map(
                        'intval',
                        array_values($item['serials'] ?? [])
                    ),
                ];
            }

            if (empty($lines)) {
                throw ValidationException::withMessages([
                    'items' => 'Select at least one product quantity greater than zero.',
                ]);
            }

            // Consistent lock order (deadlock avoid korar jonno)
            ksort($lines);

            $products = Product::query()
                ->whereIn('id', array_keys($lines))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            if ($products->count() !== count($lines)) {
                throw ValidationException::withMessages([
                    'items' => 'One or more products are no longer available.',
                ]);
            }

            $serialModel = $order = null;
            $serialModel = (new Order())->serials()->getRelated();

            $orderLines = [];
            $totalCents = 0;

            foreach ($lines as $productId => $line) {
                $product = $products->get($productId);
                $quantity = $line['quantity'];
                $key = $line['key'];

                if ($product->status !== 'active') {
                    throw ValidationException::withMessages([
                        'items' => "{$product->name} is inactive.",
                    ]);
                }

                if ($quantity < 1 || $product->stock_quantity < $quantity) {
                    throw ValidationException::withMessages([
                        'items' => "Insufficient stock for {$product->name}.",
                    ]);
                }

                $serials = [];


                if ($product->track_serials) {
                    $serials = $line['serials'];

                    if (count($serials) !== $quantity) {
                        throw ValidationException::withMessages([
                            "items.{$key}.serials" =>
                                "{$product->name}: exactly {$quantity} serial number(s) are required.",
                        ]);
                    }

                    if (count(array_unique($serials)) !== count($serials)) {
                        throw ValidationException::withMessages([
                            "items.{$key}.serials" =>
                                "{$product->name}: duplicate serial numbers are not allowed.",
                        ]);
                    }

                    if (
                        $product->serial_start === null ||
                        $product->serial_end === null
                    ) {
                        throw ValidationException::withMessages([
                            "items.{$key}.serials" =>
                                "{$product->name}: serial range is not configured.",
                        ]);
                    }

                    // Check whether any submitted serial has already been sold.
                    $taken = $serialModel->newQuery()
                        ->where('product_id', $product->id)
                        ->whereIn('serial_number', $serials)
                        ->pluck('serial_number')
                        ->map(fn ($serial) => (int) $serial)
                        ->all();

                    if (! empty($taken)) {
                        $errors = [];

                        foreach ($serials as $i => $serial) {
                            if (in_array($serial, $taken, true)) {
                                $errors["items.{$key}.serials.{$i}"] =
                                    "Serial {$serial} is already sold.";
                            }
                        }

                        throw ValidationException::withMessages($errors);
                    }

                    /*
                    * Find the last sold serial for this product.
                    * The product row is already locked by lockForUpdate().
                    */
                    $lastSoldSerial = $serialModel->newQuery()
                        ->where('product_id', $product->id)
                        ->max('serial_number');

                    // Start from serial_start for the first order.
                    $expectedSerial = $lastSoldSerial !== null
                        ? (int) $lastSoldSerial + 1
                        : (int) $product->serial_start;

                    // Ensure the requested quantity fits within the serial range.
                    $lastRequiredSerial = $expectedSerial + $quantity - 1;

                    if ($lastRequiredSerial > (int) $product->serial_end) {
                        throw ValidationException::withMessages([
                            "items.{$key}.serials" =>
                                "{$product->name}: not enough serial numbers remain.",
                        ]);
                    }

                    // Every serial must match the next expected number.
                    foreach ($serials as $i => $serial) {
                        $expected = $expectedSerial + $i;

                        if (
                            $serial < (int) $product->serial_start ||
                            $serial > (int) $product->serial_end
                        ) {
                            throw ValidationException::withMessages([
                                "items.{$key}.serials.{$i}" =>
                                    "Serial {$serial} is outside the allowed range "
                                    . "({$product->serial_start} - {$product->serial_end}).",
                            ]);
                        }

                        if ($serial !== $expected) {
                            throw ValidationException::withMessages([
                                "items.{$key}.serials.{$i}" =>
                                // serial {$expected} is required "
                                //     . "at this position.
                                    "{$product->name}: Serial numbers must be sequential.",
                            ]);
                        }
                    }
                } elseif (! empty($line['serials'])) {
                    throw ValidationException::withMessages([
                        "items.{$key}.serials" =>
                            "{$product->name} does not require serial numbers.",
                    ]);
                }


                $priceCents = (int) round((float) $product->selling_price * 100);
                $totalCents += $priceCents * $quantity;

                $orderLines[] = [
                    'product' => $product,
                    'quantity' => $quantity,
                    'priceCents' => $priceCents,
                    'serials' => $serials,
                ];
            }

            $order = Order::create([
                'user_id' => $target->id,
                'purchased_by' => $actor->id,
                'total_amount' => $totalCents / 100,
                'order_status' => 'pending',
                'payment_status' => 'pending',
            ]);

            foreach ($orderLines as $line) {
                $product = $line['product'];
                $quantity = $line['quantity'];

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'price' => $line['priceCents'] / 100,
                ]);

                if ($product->track_serials) {
                    foreach ($line['serials'] as $serial) {
                        $order->serials()->create([
                            'product_id' => $product->id,
                            'serial_number' => $serial,
                        ]);
                    }
                }

                $product->stock_quantity -= $quantity;
                $product->save();
            }

            return $order->load(['user', 'purchaser', 'items.product', 'serials.product']);
        }, attempts: 3);
    }

    public function updateStatus(int $orderId, string $newStatus): Order
    {
        return DB::transaction(function () use ($orderId, $newStatus) {
            $order = Order::query()
                ->lockForUpdate()
                ->findOrFail($orderId);

            if ($order->order_status === 'cancelled') {
                return $order;
            }

            if (
                $newStatus === 'cancelled' &&
                $order->payment_status === 'paid'
            ) {
                throw ValidationException::withMessages([
                    'order_status' =>
                        'A paid order requires refund processing before cancellation.',
                ]);
            }

            $order->order_status = $newStatus;
            $order->save();

            return $order->load([
                'user',
                'purchaser',
                'items.product',
                'serials.product',
            ]);
        }, attempts: 3);
    }

    private function superAdmin(): User
    {
        $user = User::query()
            ->whereHas('roles', function ($query) {
                $query->where('slug', 'super-admin');
            })
            ->first();

        if (! $user) {
            throw ValidationException::withMessages([
                'user_id' => 'No Super Admin account is configured.',
            ]);
        }

        return $user;
    }
}