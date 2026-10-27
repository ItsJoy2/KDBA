<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermissionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('permissions.assign') ?? false;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'module' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/'],
            'action' => ['required', 'string', 'max:100', 'regex:/^[a-z][a-z0-9_-]*$/'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function after(): array
    {
        return [
            function ($validator): void {
                $module = $this->input('module');
                $action = $this->input('action');

                if (
                    is_string($module)
                    && is_string($action)
                    && \App\Models\Permission::query()
                        ->where('module', $module)
                        ->where('action', $action)
                        ->exists()
                ) {
                    $validator->errors()->add(
                        'action',
                        'This permission already exists for the selected module.'
                    );
                }
            },
        ];
    }
}