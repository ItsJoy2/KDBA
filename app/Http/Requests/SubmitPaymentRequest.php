<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubmitPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();
        $invoice = $this->route('invoice');

        if (!$user || !$invoice) {
            return false;
        }

        // Only members and life members can submit payments.
        if (!in_array($user->member_status, [
            'member',
            'life_member',
        ], true)) {
            return false;
        }

        // Members can only pay their own invoices.
        if ((int) $invoice->user_id !== (int) $user->id) {
            return false;
        }

        // Only unpaid invoices can receive payment submissions.
        return in_array($invoice->status, [
            'pending',
            'overdue',
        ], true);
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
            'payment_gateway_id.exists' =>
                'Selected payment method is invalid or inactive.',

            'sender_mobile.required' =>
                'Sender mobile number or bank account information is required.',

            'trx_id.required' =>
                'Payment transaction ID is required.',

            'trx_id.unique' =>
                'This transaction ID has already been submitted.',

            'trx_id.regex' =>
                'Transaction ID contains invalid characters.',

            'receipt.mimes' =>
                'Receipt must be JPG, JPEG, PNG or PDF.',

            'receipt.max' =>
                'Receipt cannot exceed 5 MB.',
        ];
    }

    public function attributes(): array
    {
        return [
            'payment_gateway_id' => 'payment method',
            'sender_mobile' => 'sender mobile/account information',
            'trx_id' => 'transaction ID',
        ];
    }
}
