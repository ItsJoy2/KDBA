<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('products.view'),
            403
        );

        $products = Product::query()
            ->when($request->filled('search'), function ($query) use ($request) {
                $search = trim($request->input('search'));

                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('slug', 'like', "%{$search}%");
                });
            })
            ->when($request->filled('status'), function ($query) use ($request) {
                $query->where('status', $request->input('status'));
            })
            ->when($request->filled('stock_type'), function ($query) use ($request) {
                $query->where('stock_type', $request->input('stock_type'));
            })
            ->latest('id')
            ->paginate(20)
            ->withQueryString();

        return view('products.index', compact('products'));
    }

    public function create(Request $request): View
    {
        abort_unless(
            $request->user()->hasPermission('products.create'),
            403
        );

        return view('products.create');
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $data['created_by'] = $request->user()->id;
        $data['updated_by'] = $request->user()->id;

        /*
        |--------------------------------------------------------------------------
        | Stock Management
        |--------------------------------------------------------------------------
        */

        if ($data['stock_type'] === 'unlimited') {
            $data['stock_quantity'] = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Serial Management
        |--------------------------------------------------------------------------
        */

        if (! ($data['track_serials'] ?? false)) {
            $data['serial_start'] = null;
            $data['serial_end'] = null;
            $data['current_serial'] = null;
        } else {
            $data['current_serial'] ??= $data['serial_start'] ?? null;
        }

        Product::create($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product created successfully.');
    }

    public function edit(Request $request, Product $product): View
    {
        abort_unless(
            $request->user()->hasPermission('products.edit'),
            403
        );

        return view('products.edit', compact('product'));
    }

    public function update(
        UpdateProductRequest $request,
        Product $product
    ): RedirectResponse {
        $data = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Stock Management
        |--------------------------------------------------------------------------
        */

        if ($data['stock_type'] === 'unlimited') {
            $data['stock_quantity'] = 0;
        }

        /*
        |--------------------------------------------------------------------------
        | Serial Management
        |--------------------------------------------------------------------------
        */

        if (! ($data['track_serials'] ?? false)) {
            if ($product->orderSerials()->exists()) {
                return back()->withInput()->with(
                    'error',
                    'Serial tracking cannot be disabled because serials have already been allocated.'
                );
            }

            $data['serial_start'] = null;
            $data['serial_end'] = null;
            $data['current_serial'] = null;
        } else {
            $data['current_serial'] ??= $product->current_serial
                ?? $data['serial_start']
                ?? null;
        }

        $data['updated_by'] = $request->user()->id;

        $product->update($data);

        return redirect()
            ->route('products.index')
            ->with('success', 'Product updated successfully.');
    }

    public function show(Request $request, Product $product): View
    {
        abort_unless(
            $request->user()->hasPermission('products.view'),
            403
        );

        return view('products.show', compact('product'));
    }

    public function destroy(
        Request $request,
        Product $product
    ): RedirectResponse {
        abort_unless(
            $request->user()->hasPermission('products.delete'),
            403
        );

        if (
            $product->orderItems()->exists()
            || $product->orderSerials()->exists()
        ) {
            return back()->with(
                'error',
                'This product has order history. Deactivate it instead.'
            );
        }

        $product->delete();

        return redirect()
            ->route('products.index')
            ->with('success', 'Product deleted successfully.');
    }
}
