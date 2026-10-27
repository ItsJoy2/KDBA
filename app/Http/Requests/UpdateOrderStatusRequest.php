<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateOrderStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('orders.status_update') ?? false;
    }

    public function rules(): array
    {
        return [
            'order_status' => [
                'required',
                Rule::in([
                    'pending',
                    'processing',
                    'approved',
                    'completed',
                    'cancelled',
                ]),
            ],
        ];
    }
}
