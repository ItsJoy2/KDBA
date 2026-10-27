<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB;

class ProductSerialController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        abort_unless(
            $request->user()->hasPermission('products.manage_serials'),
            403
        );

        $data = $request->validate([
            'serials' => ['required', 'array', 'min:1', 'max:500'],
            'serials.*' => [
                'required',
                'string',
                'max:191',
                'distinct',
                Rule::unique('product_serials', 'serial_number'),
            ],
        ]);

        DB::transaction(function () use ($product, $data) {
            $lockedProduct = Product::query()
                ->lockForUpdate()
                ->findOrFail($product->id);

            if (! $lockedProduct->track_serials) {
                throw ValidationException::withMessages([
                    'serials' => 'Enable serial tracking for this product first.',
                ]);
            }

            $serials = collect($data['serials'])
                ->map(fn ($serial) => trim($serial))
                ->filter(fn ($serial) => $serial !== '')
                ->unique()
                ->values();

            if ($serials->isEmpty()) {
                throw ValidationException::withMessages([
                    'serials' => 'Enter at least one valid serial number.',
                ]);
            }

            foreach ($serials as $serial) {
                $lockedProduct->serials()->create([
                    'serial_number' => $serial,
                    'status' => 'available',
                ]);
            }

            $lockedProduct->increment('stock_quantity', $serials->count());
        });

        return back()->with('success', 'Serial numbers added successfully.');
    }
}