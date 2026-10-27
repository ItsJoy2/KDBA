<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user?->hasPermission('products.buy')) {
            return false;
        }

        // Ordering for another member requires a separate permission.
        if ($this->filled('user_id')) {
            return $user->hasPermission('products.buy_for_others');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
                'max:100',
            ],

            'items.*' => [
                'required',
                'array:product_id,quantity,serials',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(
                    fn ($query) => $query->where('status', 'active')
                ),
            ],

            'items.*.quantity' => [
                'required',
                'integer',
                'min:0',
                'max:100',
            ],

            'items.*.serials' => [
                'sometimes',
                'array',
            ],

            'items.*.serials.*' => [
                'required',
                'integer',
                'min:0',
                'distinct',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            $items = $this->input('items', []);

            if (! is_array($items)) {
                return;
            }

            $productIds = collect($items)
                ->filter(fn ($item) => is_array($item))
                ->pluck('product_id')
                ->filter()
                ->unique()
                ->values();

            $products = Product::query()
                ->whereIn('id', $productIds)
                ->where('status', 'active')
                ->get()
                ->keyBy(fn (Product $product) => (string) $product->id);

            // Serial model ta Order::serials() relation theke neya hocche.
            $serialModel = (new Order())->serials()->getRelated();

            $hasPositiveQuantity = false;

            foreach ($items as $index => $item) {
                if (! is_array($item)) {
                    continue;
                }

                $productId = $item['product_id'] ?? null;
                $product = $products->get((string) $productId);

                if (! $product) {
                    continue;
                }

                $quantityValue = $item['quantity'] ?? null;

                if (
                    ! is_numeric($quantityValue)
                    || (int) $quantityValue != $quantityValue
                    || (int) $quantityValue < 0
                    || (int) $quantityValue > 100
                ) {
                    continue;
                }

                $quantity = (int) $quantityValue;
                $hasPositiveQuantity = $hasPositiveQuantity || $quantity > 0;

                $serials = $item['serials'] ?? [];

                if (! is_array($serials)) {
                    continue;
                }

                if (! $product->track_serials) {
                    if (count($serials) > 0) {
                        $validator->errors()->add(
                            "items.$index.serials",
                            "Product {$product->name} does not require serial numbers."
                        );
                    }

                    continue;
                }

                if ($quantity === 0) {
                    if (count($serials) > 0) {
                        $validator->errors()->add(
                            "items.$index.serials",
                            "Set a quantity greater than zero before entering serial numbers for {$product->name}."
                        );
                    }

                    continue;
                }

                if (count($serials) !== $quantity) {
                    $validator->errors()->add(
                        "items.$index.serials",
                        "Product {$product->name}: exactly {$quantity} serial number(s) are required for this quantity."
                    );

                    continue;
                }

                // Valid integer serial gulo alada kore nei
                $validSerials = [];

                foreach ($serials as $serialIndex => $serial) {
                    if (
                        is_scalar($serial)
                        && is_numeric($serial)
                        && (string) (int) $serial === (string) $serial
                        && (int) $serial >= 0
                    ) {
                        $validSerials[$serialIndex] = (int) $serial;
                    }
                }

                if (empty($validSerials)) {
                    continue;
                }

                // Range check (product e serial_start / serial_end thakle)
                if ($product->serial_start !== null && $product->serial_end !== null) {
                    foreach ($validSerials as $serialIndex => $serial) {
                        if ($serial < $product->serial_start || $serial > $product->serial_end) {
                            $validator->errors()->add(
                                "items.$index.serials.$serialIndex",
                                "Serial {$serial} is outside the allowed range ({$product->serial_start} - {$product->serial_end})."
                            );
                        }
                    }
                }

                // Database e already ase kina check
                $taken = $serialModel->newQuery()
                    ->where('product_id', $product->id)
                    ->whereIn('serial_number', array_values($validSerials))
                    ->pluck('serial_number')
                    ->map(fn ($s) => (int) $s)
                    ->all();

                if (! empty($taken)) {
                    foreach ($validSerials as $serialIndex => $serial) {
                        if (in_array($serial, $taken, true)) {
                            $validator->errors()->add(
                                "items.$index.serials.$serialIndex",
                                "Serial {$serial} is already sold. You cannot buy it."
                            );
                        }
                    }
                }
            }

            if (! $hasPositiveQuantity) {
                $validator->errors()->add(
                    'items',
                    'Select at least one product with a quantity greater than zero.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'items.required' => 'Please select at least one product.',
            'items.min' => 'Please select at least one product.',
            'items.max' => 'You cannot order more than 100 product lines at once.',
            'items.*.product_id.exists' => 'A selected product is unavailable.',
            'items.*.product_id.distinct' => 'Do not submit the same product more than once.',
            'items.*.quantity.min' => 'Quantity cannot be negative.',
            'items.*.quantity.max' => 'Quantity cannot exceed 100.',
            'items.*.serials.*.distinct' => 'Duplicate serial numbers are not allowed in one order.',
        ];
    }
}