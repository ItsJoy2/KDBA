<?php

namespace App\Http\Requests;

use App\Models\Invoice;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $invoice = $this->route('invoice');

        return auth()->check()
            && $invoice instanceof Invoice
            && (int) $invoice->user_id === (int) auth()->id();
    }

    public function rules(): array
    {
        return [
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
            'payment_gateway_id.required' => 'Please select a payment gateway.',
            'payment_gateway_id.exists' => 'The selected payment gateway is unavailable.',

            'sender_mobile.required' => 'Sender account/mobile number is required.',

            'trx_id.required' => 'Transaction ID is required.',
            'trx_id.unique' => 'This transaction ID has already been used.',
            'trx_id.regex' => 'Transaction ID may contain only letters, numbers, hyphens and underscores.',

            'receipt.mimes' => 'Receipt must be JPG, JPEG, PNG or PDF.',
            'receipt.max' => 'Receipt cannot exceed 5 MB.',
        ];
    }
}
