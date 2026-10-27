<?php

namespace App\Http\Requests;

use App\Models\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        $user = $this->user();

        if (! $user || ! $user->hasPermission('roles.edit')) {
            return false;
        }

        $role = $this->route('role');

        if (! $role instanceof Role) {
            return false;
        }

        // Keep only the Super Admin role protected.
        return $role->slug !== 'super-admin';
    }

    protected function prepareForValidation(): void
    {
        if (! $this->exists('permissions')) {
            $this->merge([
                'permissions' => [],
            ]);
        }
    }

    public function rules(): array
    {
        $role = $this->route('role');

        return [
            'name' => [
                'required',
                'string',
                'max:100',
                Rule::unique('roles', 'name')->ignore($role->getKey()),
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
            'name.unique' => 'Another role already uses this name.',
            'permissions.array' => 'The permissions selection is invalid.',
            'permissions.max' => 'You cannot select more than 500 permissions.',
            'permissions.*.exists' => 'One or more selected permissions do not exist.',
            'permissions.*.distinct' => 'Duplicate permissions are not allowed.',
        ];
    }
}
