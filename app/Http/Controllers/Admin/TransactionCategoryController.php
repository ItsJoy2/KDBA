<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreTransactionCategoryRequest;
use App\Http\Requests\UpdateTransactionCategoryRequest;
use App\Models\TransactionCategory;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class TransactionCategoryController extends Controller
{
    public function index(): View
    {
        $categories = TransactionCategory::query()
            ->withCount('transactions')
            ->orderBy('type')
            ->orderBy('name')
            ->paginate(20);

        return view('admin.transaction-categories.index', compact('categories'));
    }

    public function store(
        StoreTransactionCategoryRequest $request
    ): RedirectResponse {
        $data = $request->validated();

        // Admin-created categories must never be system categories.
        $data['is_system'] = false;

        TransactionCategory::create($data);

        return back()->with(
            'success',
            'Transaction category created successfully.'
        );
    }

    public function update(
        UpdateTransactionCategoryRequest $request,
        TransactionCategory $category
    ): RedirectResponse {
        // System categories cannot be modified.
        if ($category->is_system) {
            return back()->with(
                'error',
                'System categories cannot be edited.'
            );
        }

        $data = $request->validated();

        // Prevent changing category type when transactions already use it.
        if (
            isset($data['type']) &&
            $data['type'] !== $category->type &&
            $category->transactions()->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'type' => 'Category type cannot be changed because transactions already use this category.',
                ]);
        }

        // Never allow changing system status through request data.
        unset($data['is_system']);

        $category->update($data);

        return back()->with(
            'success',
            'Transaction category updated successfully.'
        );
    }

    public function destroy(
        TransactionCategory $category
    ): RedirectResponse {
        // System categories cannot be deleted.
        if ($category->is_system) {
            return back()->with(
                'error',
                'System categories cannot be deleted.'
            );
        }

        if ($category->transactions()->exists()) {
            return back()->with(
                'error',
                'This category is already used by financial transactions. Deactivate it instead.'
            );
        }

        $category->delete();

        return back()->with(
            'success',
            'Transaction category deleted successfully.'
        );
    }
}
