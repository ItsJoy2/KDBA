<?php

namespace App\Http\Requests\Vakalatnama;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateVakalatnamaStickerSettingsRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        return $user !== null
            && (
                $user->hasPermission('vakalatnama.settings')
                || $user->hasPermission('settings.manage')
            );
    }

    public function rules(): array
    {
        return [
            'price' => [
                'required',
                'numeric',
                'min:0.01',
                'max:9999999999.99',
                'decimal:0,2',
            ],

            'enabled' => [
                'required',
                Rule::in(['0', '1', 0, 1, true, false]),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'price.required' => 'Sticker unit price is required.',
            'price.min' => 'Sticker price must be greater than zero.',
            'enabled.required' => 'Please specify whether stickers are enabled.',
        ];
    }
}