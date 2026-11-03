<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyTransactionRequest extends FormRequest
{
    public function authorize(): bool
    {
        // RBAC middleware:
        // permission:transactions.verify
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'status' => [
                'required',
                'string',
                Rule::in([
                    'approved',
                    'rejected',
                ]),
            ],

            'rejection_reason' => [
                'required_if:status,rejected',
                'nullable',
                'string',
                'max:2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' =>
                'Please select an action.',

            'status.in' =>
                'Invalid transaction verification status.',

            'rejection_reason.required_if' =>
                'A rejection reason is required when rejecting a payment.',
        ];
    }
}
