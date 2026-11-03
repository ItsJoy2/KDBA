<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // RBAC middleware: permission:invoices.create
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => [
                'required',
                'integer',
                Rule::exists('users', 'id')
                    ->whereIn('member_status', [
                        'member',
                        'life_member',
                    ]),
            ],

            'invoice_type' => [
                'required',
                'string',
                Rule::in([
                    'registration',
                    'yearly_renewal',
                ]),
            ],

            'cycle_key' => [
                'required',
                'string',
                'max:30',
            ],

            'member_status' => [
                'required',
                Rule::in([
                    'member',
                    'life_member',
                ]),
            ],

            'title' => [
                'required',
                'string',
                'max:255',
            ],

            'description' => [
                'nullable',
                'string',
                'max:5000',
            ],

            'amount' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0.01',
                'max:9999999999.99',
            ],

            'due_date' => [
                'nullable',
                'date',
                'after_or_equal:today',
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator) {
                $type = $this->input('invoice_type');
                $cycle = $this->input('cycle_key');

                if (
                    $type === 'registration' &&
                    $cycle !== 'registration'
                ) {
                    $validator->errors()->add(
                        'cycle_key',
                        'Registration invoices must use the registration cycle.'
                    );
                }

                if (
                    $type === 'yearly_renewal' &&
                    !preg_match('/^\d{4}$/', (string) $cycle)
                ) {
                    $validator->errors()->add(
                        'cycle_key',
                        'Yearly renewal cycle must be a valid four-digit year.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'user_id.exists' =>
                'Selected user is not an eligible member.',

            'amount.min' =>
                'Invoice amount must be greater than zero.',

            'invoice_type.in' =>
                'Invalid invoice type.',

            'member_status.in' =>
                'Invalid membership category.',
        ];
    }
}
