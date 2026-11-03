<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentGatewayRequest extends FormRequest
{
    public function authorize(): bool
    {
        // RBAC middleware: permission:gateways.manage
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $gatewayId = $this->route('gateway')?->id
            ?? $this->route('gateway');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('payment_gateways', 'name')
                    ->ignore($gatewayId),
            ],

            'type' => [
                'required',
                'string',
                Rule::in([
                    'mobile_banking',
                    'bank',
                    'other',
                ]),
            ],

            'account_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'instruction' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            'sort_order' => [
                'nullable',
                'integer',
                'min:0',
                'max:65535',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Payment gateway name is required.',

            'name.unique' => 'This payment gateway already exists.',

            'type.in' => 'Invalid payment gateway type.',

            'is_active.required' =>
                'Please specify whether this gateway is active.',
        ];
    }
}
