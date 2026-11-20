<?php

namespace App\Http\Requests;

use App\Models\Order;
use App\Models\PaymentGateway;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitOrderPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $order = $this->route('order');

        return auth()->check()
            && $order instanceof Order
            && (int) $order->user_id === (int) auth()->id()
            && auth()->user()->hasPermission('orders.view_own');
    }

    public function rules(): array
    {
        $isCash = $this->isCashGateway();

        return [
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
                ...(! $isCash
                    ? [Rule::unique('transactions', 'trx_id')]
                    : []),
            ],

            'receipt' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],
        ];
    }

    private function isCashGateway(): bool
    {
        $gateway = PaymentGateway::query()
            ->whereKey($this->input('payment_gateway_id'))
            ->where('is_active', true)
            ->first();

        return $gateway
            && strtolower($gateway->type ?? '') === 'cash';
    }
}
