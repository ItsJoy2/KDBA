<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreManualTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('transactions.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'category_id' => [
                'required',
                'integer',
                Rule::exists('transaction_categories', 'id')
                    ->where('is_active', true),
            ],

            'amount' => [
                'required',
                'numeric',
                'gt:0',
                'decimal:0,2',
                'max:999999999999.99',
            ],

            'payment_gateway_id' => [
                'nullable',
                'integer',
                Rule::exists('payment_gateways', 'id')
                    ->where('is_active', true),
            ],

            'description' => [
                'required',
                'string',
                'max:5000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.required' => 'Please select a transaction category.',
            'category_id.exists' => 'The selected category is invalid or inactive.',

            'amount.required' => 'Transaction amount is required.',
            'amount.numeric' => 'Transaction amount must be numeric.',
            'amount.gt' => 'Transaction amount must be greater than zero.',
            'amount.decimal' => 'Transaction amount may have up to 2 decimal places.',

            'payment_gateway_id.exists' => 'The selected payment gateway is invalid or inactive.',

            'description.required' => 'Transaction description is required.',
        ];
    }
}
