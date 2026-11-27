<?php

namespace App\Http\Requests\Vakalatnama;

use App\Models\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVakalatnamaStickerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
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

            'trx_id' => [
                'nullable',
                'string',
                'max:100',
            ],

            'sender_mobile' => [
                'nullable',
                'string',
                'max:30',
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'remarks' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $gateway = PaymentGateway::query()
                ->whereKey($this->input('payment_gateway_id'))
                ->where('is_active', true)
                ->first();

            if (! $gateway) {
                return;
            }

            // Cash transactions do not require external
            // transaction IDs or sender mobile numbers.
            if ($gateway->type === 'cash') {
                return;
            }

            if (! $this->filled('trx_id')) {
                $validator->errors()->add(
                    'trx_id',
                    'Transaction ID is required for this payment gateway.'
                );
            }

            if (! $this->filled('sender_mobile')) {
                $validator->errors()->add(
                    'sender_mobile',
                    'Sender mobile number is required.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'payment_gateway_id.required' => 'Please select a payment gateway.',
            'payment_gateway_id.exists' => 'The selected payment gateway is unavailable.',
            'receipt.max' => 'Receipt file must not exceed 5 MB.',
        ];
    }
}
