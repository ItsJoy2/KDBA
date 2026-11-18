<?php

namespace App\Http\Requests\Admin;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdateUserRequest extends FormRequest
{
    /**
     * Authorize update request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.edit') ?? false;
    }

    /**
     * Get effective member status.
     */
    private function effectiveMemberStatus(): ?string
    {
        $routeUser = $this->route('user');

        return $this->input(
            'member_status',
            $routeUser instanceof User
                ? $routeUser->member_status
                : null
        );
    }

    /**
     * Determine whether membership type is required.
     *
     * Existing members can update unrelated information
     * without submitting member_type again.
     */
    private function shouldRequireMemberType(): bool
    {
        return $this->effectiveMemberStatus() === User::MEMBER_STATUS_MEMBER
            && (
                $this->exists('member_status')
                || $this->exists('member_type')
            );
    }

    /**
     * Get validation rules.
     */
    public function rules(): array
    {
        $routeUser = $this->route('user');

        $userId = $routeUser instanceof User
            ? $routeUser->id
            : (is_numeric($routeUser) ? (int) $routeUser : null);

        $effectiveStatus = $this->effectiveMemberStatus();

        /*
         * Only actual members can be assigned as lawyers.
         */
        $lawyerRule = Rule::exists('users', 'id')
            ->whereIn('member_status', [
                User::MEMBER_STATUS_MEMBER,
            ]);

        if ($userId !== null) {
            $lawyerRule->where('id', '!=', $userId);
        }

        return [

            // Basic Information
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

            // Personal Information
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

            // NID / Passport
            'nid_passport_number' => [
                'sometimes',
                'nullable',
                'string',
                'max:17',
                'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17}|[A-Za-z]{2}[0-9]{7})$/',
                Rule::unique('users', 'nid_passport_number')
                    ->ignore($userId),
            ],

            // Profile Photo
            'photo' => [
                'sometimes',
                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',
            ],

            // NID Images
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

            // Membership Information
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

            // Approval Status
            'approval_status' => [
                'sometimes',
                'required',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                ]),
            ],

            // Member Status
            'member_status' => [
                'sometimes',
                'nullable',
                Rule::in([
                    ...User::MEMBER_STATUSES,

                    // Allow existing legacy status unchanged.
                    ...(
                        $routeUser instanceof User
                        && !in_array(
                            $routeUser->member_status,
                            User::MEMBER_STATUSES,
                            true
                        )
                            ? [$routeUser->member_status]
                            : []
                    ),
                ]),
            ],

            // Membership Type
            'member_type' => [
                'nullable',

                Rule::requiredIf(
                    fn () => $this->shouldRequireMemberType()
                ),

                Rule::prohibitedIf(
                    fn () => $this->effectiveMemberStatus()
                        !== User::MEMBER_STATUS_MEMBER
                ),

                Rule::in(User::MEMBER_TYPES),
            ],

            // Chamber Room Number
            'chamber_room_no' => [
                'sometimes',
                'nullable',
                'string',
                'max:50',
                'required_with:chamber_type',

                Rule::prohibitedIf(
                    fn () => $effectiveStatus
                        !== User::MEMBER_STATUS_MEMBER
                ),
            ],

            // Chamber Type
            'chamber_type' => [
                'sometimes',
                'nullable',
                Rule::in(User::CHAMBER_TYPES),
                'required_with:chamber_room_no',

                Rule::prohibitedIf(
                    fn () => $effectiveStatus
                        !== User::MEMBER_STATUS_MEMBER
                ),
            ],

            // Lawyer Relationship
            'lawyer_id' => [
                'sometimes',
                'nullable',
                'integer',

                Rule::requiredIf(
                    fn () => $effectiveStatus
                        === User::MEMBER_STATUS_LAWYER_ASSISTANT
                ),

                Rule::prohibitedIf(
                    fn () => !in_array(
                        $effectiveStatus,
                        [
                            User::MEMBER_STATUS_LAWYER_ASSISTANT,
                            User::MEMBER_STATUS_APPRENTICE,
                        ],
                        true
                    )
                ),

                $lawyerRule,
            ],

            // Role Selection
            'roles' => [
                'sometimes',
                $this->user()?->hasPermission('users.assign_roles')
                    ? 'array'
                    : 'prohibited',
            ],

            'roles.*' => [
                'integer',
                'distinct',
                'exists:roles,id',
            ],

            'roles_selection_submitted' => [
                'sometimes',
                $this->user()?->hasPermission('users.assign_roles')
                    ? 'boolean'
                    : 'prohibited',
            ],
        ];
    }

    /**
     * Additional validation.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {

                // Approval Permission Check
                if (
                    $this->exists('approval_status')
                    && !$this->user()?->hasPermission('users.approve')
                ) {
                    $validator->errors()->add(
                        'approval_status',
                        'You do not have permission to change approval status.'
                    );
                }

                // Member Status Permission Check
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

    /**
     * Custom validation messages.
     */
    public function messages(): array
    {
        return [

            'email.unique' => 'This email address is already registered.',
            'mobile.unique' => 'This mobile number is already registered.',

            'password.confirmed' => 'The password confirmation does not match.',

            'approval_status.in' => 'Please select a valid approval status.',

            'member_status.in' => 'Please select a valid member status.',

            'member_type.required' => 'Please select a membership type.',
            'member_type.in' => 'Please select a valid membership type.',
            'member_type.prohibited' => 'Membership type is only applicable to members.',

            'chamber_room_no.required_with' => 'Chamber room number is required.',
            'chamber_room_no.prohibited' => 'Only members can have chamber allocation.',

            'chamber_type.required_with' => 'Chamber type is required.',
            'chamber_type.in' => 'Please select a valid chamber type.',
            'chamber_type.prohibited' => 'Only members can have chamber allocation.',

            'lawyer_id.required' => 'Please select a lawyer for this lawyer assistant.',
            'lawyer_id.exists' => 'The selected lawyer is invalid or is not an eligible member.',
            'lawyer_id.prohibited' => 'This user cannot have an assigned lawyer.',

            'roles.array' => 'The roles field must be an array.',
            'roles.*.integer' => 'Each selected role must be valid.',
            'roles.*.distinct' => 'A role cannot be selected more than once.',
            'roles.*.exists' => 'One or more selected roles do not exist.',
        ];
    }
}
