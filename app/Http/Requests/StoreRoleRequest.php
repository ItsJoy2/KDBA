<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('roles.create') ?? false;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('permissions')) {
            $this->merge(['permissions' => []]);
        }
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name'),
            ],

            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],

            'permissions' => [
                'sometimes',
                'array',
                'max:500',
            ],

            'permissions.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('permissions', 'id'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Please enter a role name.',
            'name.unique' => 'A role with this name already exists.',
            'permissions.array' => 'The permissions selection is invalid.',
            'permissions.max' => 'You cannot select more than 500 permissions.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
            'permissions.*.distinct' => 'Duplicate permissions are not allowed.',
        ];
    }
}
