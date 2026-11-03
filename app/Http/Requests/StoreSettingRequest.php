<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        // RBAC middleware: permission:settings.manage
        // and permission:fees.manage
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'registration_fee' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],

            'yearly_fee_member' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],

            'yearly_fee_life_member' => [
                'required',
                'numeric',
                'decimal:0,2',
                'min:0',
                'max:9999999999.99',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'registration_fee.required' =>
                'Initial membership registration fee is required.',

            'yearly_fee_member.required' =>
                'Yearly membership fee is required.',

            'yearly_fee_life_member.required' =>
                'Yearly life membership fee is required.',

            '*.decimal' =>
                'Fee amount can contain a maximum of two decimal places.',

            '*.min' =>
                'Fee amount cannot be negative.',
        ];
    }
}
