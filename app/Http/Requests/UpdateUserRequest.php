<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.edit') ?? false;
    }

    public function rules(): array
    {
        $routeUser = $this->route('user');

        $userId = $routeUser instanceof User
            ? $routeUser->id
            : (is_numeric($routeUser) ? (int) $routeUser : null);

        $currentStatus = $routeUser instanceof User
            ? $routeUser->member_status
            : ($userId
                ? User::whereKey($userId)->value('member_status')
                : null);

        $effectiveStatus = $this->input(
            'member_status',
            $currentStatus
        );

        $lawyerRule = Rule::exists('users', 'id')
            ->whereIn('member_status', ['member', 'life_member']);

        if ($userId !== null) {
            $lawyerRule->where('id', '!=', $userId);
        }

        return [
            'full_name' => [
                'sometimes',
                'required',
                'string',
                'max:150',
            ],

            'email' => [
                'sometimes',
                'nullable',
                'email:rfc',
                'max:254',
                Rule::unique('users', 'email')->ignore($userId),
            ],

            'mobile' => [
                'sometimes',
                'required',
                'string',
                'max:11',
                'regex:/^01[3-9][0-9]{8}$/',
                Rule::unique('users', 'mobile')->ignore($userId),
            ],

            'password' => [
                'sometimes',
                'nullable',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            'father_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'mother_name' => [
                'sometimes',
                'nullable',
                'string',
                'max:150',
            ],

            'dob' => [
                'sometimes',
                'nullable',
                'date',
                'before:today',
            ],

            'gender' => [
                'sometimes',
                'nullable',
                Rule::in(['male', 'female', 'other']),
            ],

            'blood_group' => [
                'sometimes',
                'nullable',
                Rule::in([
                    'A+', 'A-', 'B+', 'B-',
                    'AB+', 'AB-', 'O+', 'O-',
                ]),
            ],

            'present_address' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],

            'permanent_address' => [
                'sometimes',
                'nullable',
                'string',
                'max:2000',
            ],

            'nid_passport_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:17',
                'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17}|[A-Za-z]{2}[0-9]{7})$/',
                Rule::unique('users', 'nid_passport_number')
                    ->ignore($userId),
            ],

            'photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            'nid_images' => [
                'sometimes',
                'nullable',
                'array',
            ],

            'nid_images.front' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'nid_images.back' => [
                'sometimes',
                'nullable',
                'file',
                'mimes:jpg,jpeg,png,pdf',
                'max:5120',
            ],

            'membership_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('users', 'membership_number')
                    ->ignore($userId),
            ],

            'bar_join_date' => [
                'sometimes',
                'nullable',
                'date',
            ],

            'sanad_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
            ],

            'sanad_date' => [
                'sometimes',
                'nullable',
                'date',
            ],
            
            'approval_status' => [
                'sometimes',
                'required',
                Rule::in(['pending', 'approved', 'rejected']),
            ],

            'member_status' => [
                'sometimes',
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

            'lawyer_id' => [ 'nullable', 'integer',
                Rule::prohibitedIf(
                    ! in_array(
                        $effectiveStatus,
                        ['lawyer_assistant', 'apprentice'],
                        true
                    )
                ),
                $lawyerRule,
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
                ) {
                    $validator->errors()->add(
                        'approval_status',
                        'You do not have permission to change approval status.'
                    );
                }

                if (
                    $this->exists('member_status')
                    && !$this->user()?->hasPermission('users.change_status')
                ) {
                    $validator->errors()->add(
                        'member_status',
                        'You do not have permission to change member status.'
                    );
                }
            },
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'This email address is already registered.',
            'mobile.unique' => 'This mobile number is already registered.',
            'password.confirmed' => 'The password confirmation does not match.',
            'approval_status.in' => 'Please select a valid approval status.',
            'member_status.in' => 'Please select a valid member status.',

            'roles.array' => 'The roles field must be an array.',
            'roles.*.integer' => 'Each selected role must be valid.',
            'roles.*.distinct' => 'A role cannot be selected more than once.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
