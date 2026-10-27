<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('products.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (blank($this->input('slug')) && filled($this->input('name'))) {
            $this->merge(['slug' => Str::slug($this->input('name'))]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['required', 'string', 'max:180', 'alpha_dash', 'unique:products,slug'],
            'description' => ['nullable', 'string', 'max:5000'],
            'buy_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'selling_price' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'track_serials' => ['sometimes', 'boolean'],
            'serial_start' => ['nullable', 'integer', 'min:0'],
            'serial_end' => ['nullable', 'integer', 'gte:serial_start'],
            'current_serial' => ['nullable', 'integer', 'gte:serial_start', 'lte:serial_end'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ];
    }
}