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
        TransactionCategory::create($request->validated());

        return back()->with('success', 'Transaction category created successfully.');
    }

    public function update(
        UpdateTransactionCategoryRequest $request,
        TransactionCategory $category
    ): RedirectResponse {
        $data = $request->validated();

        // Prevent changing income to expense or vice versa
        // when existing financial transactions use this category.
        if (
            isset($data['type'])
            && $data['type'] !== $category->type
            && $category->transactions()->exists()
        ) {
            return back()
                ->withInput()
                ->withErrors([
                    'type' => 'Category type cannot be changed because transactions already use this category.',
                ]);
        }

        $category->update($data);

        return back()->with('success', 'Transaction category updated successfully.');
    }

    public function destroy(
        TransactionCategory $category
    ): RedirectResponse {
        if ($category->transactions()->exists()) {
            return back()->with(
                'error',
                'This category is already used by financial transactions. Deactivate it instead.'
            );
        }

        $category->delete();

        return back()->with('success', 'Transaction category deleted successfully.');
    }
}
