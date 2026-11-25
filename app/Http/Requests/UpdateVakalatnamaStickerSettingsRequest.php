<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateVakalatnamaStickerSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission(
            'vakalatnama-sticker-settings'
        ) ?? false;
    }

    public function rules(): array
    {
        return [
            'price' => [
                'required',
                'numeric',
                'gt:0',
                'max:1000000',
                'decimal:0,2',
            ],

            'enabled' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => 'Sticker price is required.',
            'price.numeric' => 'Sticker price must be numeric.',
            'price.gt' => 'Sticker price must be greater than zero.',
            'enabled.required' => 'Please select sticker availability.',
        ];
    }
}
