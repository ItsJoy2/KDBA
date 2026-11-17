<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTransactionCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('finance_categories.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:150',
            ],

            'slug' => [
                'required',
                'string',
                'max:180',
                'alpha_dash',
                Rule::unique('transaction_categories', 'slug'),
            ],

            'type' => [
                'required',
                Rule::in(['income', 'expense']),
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'sometimes',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'slug.required' => 'Category slug is required.',
            'slug.unique' => 'This category slug already exists.',
            'slug.alpha_dash' => 'Slug may contain only letters, numbers, dashes and underscores.',
            'type.required' => 'Please select a category type.',
            'type.in' => 'Category type must be either income or expense.',
        ];
    }
}
