<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        // Admin-created users default to approved.
        if (!$this->user()?->hasPermission('users.approve')) {
            $this->merge(['approval_status' => 'approved']);
        } elseif (!$this->has('approval_status')) {
            $this->merge(['approval_status' => 'approved']);
        }

        // Default member status for a new record.
        if (!$this->has('member_status')) {
            $this->merge(['member_status' => 'member']);
        }
    }

    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.create') ?? false;
    }

    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'max:150'],

            'email' => [
                'nullable',
                'email:rfc',
                'max:254',
                'unique:users,email',
            ],

            'mobile' => [
                'required',
                'string',
                'max:30',
                'unique:users,mobile',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)->mixedCase()->numbers()->symbols(),
            ],

            'father_name' => ['nullable', 'string', 'max:150'],
            'mother_name' => ['nullable', 'string', 'max:150'],
            'dob' => ['nullable', 'date', 'before:today'],

            'gender' => [
                'nullable',
                Rule::in(['male', 'female', 'other']),
            ],

            'blood_group' => [
                'nullable',
                Rule::in([
                    'A+', 'A-', 'B+', 'B-',
                    'AB+', 'AB-', 'O+', 'O-',
                ]),
            ],

            'present_address' => ['nullable', 'string', 'max:2000'],
            'permanent_address' => ['nullable', 'string', 'max:2000'],

            'nid_passport_number' => [
                'nullable',
                'string',
                'max:100',
                'unique:users,nid_passport_number',
            ],

            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            'nid_images' => ['nullable', 'array'],

            'nid_images.front' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'nid_images.back' => [
                'nullable',
                'file',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            'membership_number' => [
                'nullable',
                'string',
                'max:100',
                'unique:users,membership_number',
            ],

            'bar_join_date' => ['nullable', 'date'],
            'sanad_number' => ['nullable', 'string', 'max:100'],
            'sanad_date' => ['nullable', 'date'],

            // Role selection permission
            'roles' => [
                $this->user()?->hasPermission('users.assign_roles')
                    ? 'sometimes'
                    : 'prohibited',
                'array',
            ],

            'roles_selection_submitted' => [
                $this->user()?->hasPermission('users.assign_roles')
                    ? 'sometimes'
                    : 'prohibited',
                'boolean',
            ],

            'roles.*' => [
                'integer',
                'distinct',
                'exists:roles,id',
            ],

            'approval_status' => [
                'required',
                Rule::in(['pending', 'approved', 'rejected']),
            ],

            'member_status' => [
                'required',
                Rule::in([
                    'member',
                    'inactive',
                    'suspended',
                    'deceased',
                    'life_member',
                    'apprentice',
                    'lawyer_assistant',
                    'office_staff',
                ]),
            ],

            'lawyer_id' => [
                'nullable',
                'integer',
                'prohibited_unless:member_status,lawyer_assistant,apprentice',
                Rule::exists('users', 'id')
                    ->whereIn('member_status', ['member', 'life_member']),
            ],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if (
                    $this->exists('approval_status')
                    && !$this->user()?->hasPermission('users.approve')
                    && $this->input('approval_status') !== 'approved'
                ) {
                    $validator->errors()->add(
                        'approval_status',
                        'You do not have permission to set the approval status.'
                    );
                }

                if (
                    $this->exists('member_status')
                    && !$this->user()?->hasPermission('users.change_status')
                    && $this->input('member_status') !== 'member'
                ) {
                    $validator->errors()->add(
                        'member_status',
                        'You do not have permission to set this member status.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'full_name.required' => 'The full name is required.',
            'mobile.required' => 'A mobile number is required.',
            'mobile.unique' => 'This mobile number is already registered.',
            'email.unique' => 'This email address is already registered.',
            'password.confirmed' => 'The password confirmation does not match.',
            'approval_status.in' => 'Please select a valid approval status.',
            'member_status.in' => 'Please select a valid member status.',
            'roles.array' => 'The roles field must be an array.',
            'roles.*.integer' => 'Each selected role must be valid.',
            'roles.*.distinct' => 'A role cannot be selected more than once.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
            'nid_images.front.mimes' => 'The front NID image must be JPG or PNG.',
            'nid_images.back.mimes' => 'The back NID image must be JPG or PNG.',
        ];
    }
}
