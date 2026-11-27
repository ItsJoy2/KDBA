<?php

namespace App\Http\Requests\Vakalatnama;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class VerifyVakalatnamaStickerPaymentRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && (
                $user->can('transactions.verify')
                || $user->can('vakalatnama.verify')
            );
    }

    public function rules(): array
    {
        return [
            'decision' => [
                'required',
                Rule::in(['approve', 'reject']),
            ],

            'rejection_reason' => [
                'nullable',
                'required_if:decision,reject',
                'string',
                'max:1000',
            ],
        ];
    }
}
