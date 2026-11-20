<?php

namespace App\Http\Requests;

use App\Models\PaymentGateway;
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

        if ($this->input('status') === 'approved') {
            return $user->hasPermission('transactions.verify');
        }

        return true;
    }

    public function rules(): array
    {
        $isCash = $this->isCashGateway();

        return [
            /*
            |--------------------------------------------------------------------------
            | Invoice / Order Reference
            |--------------------------------------------------------------------------
            | Exactly one reference must be provided.
            */

            'invoice_id' => [
                'nullable',
                'required_without:order_id',
                'prohibits:order_id',
                'integer',
                'exists:invoices,id',
            ],

            'order_id' => [
                'nullable',
                'required_without:invoice_id',
                'prohibits:invoice_id',
                'integer',
                'exists:orders,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | Payment Details
            |--------------------------------------------------------------------------
            */

            'amount' => [
                'required',
                'numeric',
                'min:0.01',
            ],

            'payment_gateway_id' => [
                'required',
                'integer',
                Rule::exists('payment_gateways', 'id')
                    ->where('is_active', true),
            ],

            'sender_mobile' => [
                Rule::requiredIf(! $isCash),
                'nullable',
                'string',
                'max:100',
            ],

            'trx_id' => [
                Rule::requiredIf(! $isCash),
                'nullable',
                'string',
                'min:4',
                'max:150',
                'regex:/^[A-Za-z0-9_-]+$/',
                ...(! $isCash ? [
                    Rule::unique('transactions', 'trx_id'),
                ] : []),
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | Transaction Status
            |--------------------------------------------------------------------------
            */

            'status' => [
                'required',
                Rule::in(['pending', 'approved']),
            ],
        ];
    }

    /**
     * Determine whether the selected gateway is Cash.
     */
    private function isCashGateway(): bool
    {
        $gatewayId = $this->input('payment_gateway_id');

        if (! $gatewayId) {
            return false;
        }

        return PaymentGateway::query()
            ->whereKey($gatewayId)
            ->where('type', 'cash')
            ->where('is_active', true)
            ->exists();
    }

    public function messages(): array
    {
        return [
            'invoice_id.required_without' =>
                'Please select an invoice or order.',

            'invoice_id.prohibits' =>
                'Invoice and order cannot be selected together.',

            'order_id.required_without' =>
                'Please select an order or invoice.',

            'order_id.prohibits' =>
                'Order and invoice cannot be selected together.',

            'amount.required' =>
                'Payment amount is required.',

            'payment_gateway_id.required' =>
                'Please select a payment gateway.',

            'payment_gateway_id.exists' =>
                'The selected payment gateway is unavailable.',

            'sender_mobile.required' =>
                'Sender account/mobile number is required.',

            'trx_id.required' =>
                'Transaction ID is required.',

            'trx_id.regex' =>
                'Transaction ID format is invalid.',

            'trx_id.unique' =>
                'This transaction ID has already been used.',

            'status.required' =>
                'Please select a payment status.',

            'status.in' =>
                'Invalid payment status selected.',
        ];
    }
}
