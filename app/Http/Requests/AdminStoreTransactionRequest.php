<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AdminStoreTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user) {
            return false;
        }

        $canCreate = $user->hasPermission('transactions.create')
            || $user->hasPermission('invoices.pay');

        if (! $canCreate) {
            return false;
        }

        // Auto-approval requires an additional permission.
        if ($this->boolean('auto_approve')) {
            return $user->hasPermission('transactions.verify');
        }

        return true;
    }

    public function rules(): array
    {
        return [
            'invoice_id' => [
                'required',
                'integer',
                Rule::exists('invoices', 'id')
                    ->whereIn('status', ['pending', 'overdue']),
            ],

            'payment_gateway_id' => [
                'required',
                'integer',
                Rule::exists('payment_gateways', 'id')
                    ->where('is_active', true),
            ],

            'sender_mobile' => [
                'required',
                'string',
                'max:100',
            ],

            'trx_id' => [
                'required',
                'string',
                'min:4',
                'max:150',
                'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('transactions', 'trx_id'),
            ],

            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'gt:0',
                'max:9999999999.99',
            ],

            'auto_approve' => [
                'sometimes',
                'boolean',
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'invoice_id.required' => 'Please select an invoice.',
            'invoice_id.exists' => 'The selected invoice is unavailable or already paid.',

            'payment_gateway_id.required' => 'Please select a payment gateway.',
            'payment_gateway_id.exists' => 'The selected payment gateway is unavailable.',

            'sender_mobile.required' => 'Sender account/mobile number is required.',

            'trx_id.required' => 'Transaction ID is required.',
            'trx_id.unique' => 'This transaction ID has already been used.',
            'trx_id.regex' => 'Transaction ID may contain only letters, numbers, hyphens and underscores.',

            'amount.required' => 'Payment amount is required.',
            'amount.decimal' => 'Amount can contain a maximum of two decimal places.',
            'amount.gt' => 'Payment amount must be greater than zero.',

            'receipt.mimes' => 'Receipt must be JPG, JPEG, PNG or PDF.',
            'receipt.max' => 'Receipt cannot exceed 5 MB.',
        ];
    }
}
