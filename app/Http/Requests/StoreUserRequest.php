<?php

namespace App\Http\Requests;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class StoreUserRequest extends FormRequest
{
    /**
     * Prepare input before validation.
     */
    protected function prepareForValidation(): void
    {
        // Admin-created users default to approved.
        if (!$this->user()?->hasPermission('users.approve')) {
            $this->merge([
                'approval_status' => 'approved',
            ]);
        } elseif (!$this->has('approval_status')) {
            $this->merge([
                'approval_status' => 'approved',
            ]);
        }

        /*
         * Do not assign a default member_status.
         *
         * NULL member_status represents a General Portal User.
         */
    }

    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()?->hasPermission('users.create') ?? false;
    }

    /**
     * Get the validation rules.
     */
    public function rules(): array
    {
        $memberStatus = $this->input('member_status');

        return [

            // Basic Information
            'full_name' => [
                'required',
                'string',
                'max:150',
            ],

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
                'regex:/^01[3-9][0-9]{8}$/',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->mixedCase()
                    ->numbers()
                    ->symbols(),
            ],

            // Personal Information
            'father_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'mother_name' => [
                'nullable',
                'string',
                'max:150',
            ],

            'dob' => [
                'nullable',
                'date',
                'before:today',
            ],

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

            'present_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            'permanent_address' => [
                'nullable',
                'string',
                'max:2000',
            ],

            // NID / Passport Information
            'nid_passport_number' => [
                'nullable',
                'string',
                'max:17',
                'unique:users,nid_passport_number',
                'regex:/^(?:[0-9]{10}|[0-9]{13}|[0-9]{17}|[A-Za-z]{2}[0-9]{7})$/',
            ],

            // Profile Photo
            'photo' => [
                'nullable',
                'image',
                'mimes:jpg,jpeg,png',
                'max:2048',
            ],

            // NID Images
            'nid_images' => [
                'nullable',
                'array',
            ],

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

            // Membership Information
            'membership_number' => [
                'nullable',
                'string',
                'max:100',
                'unique:users,membership_number',
            ],

            'bar_join_date' => [
                'nullable',
                'date',
            ],

            'sanad_number' => [
                'nullable',
                'string',
                'max:100',
            ],

            'sanad_date' => [
                'nullable',
                'date',
            ],

            // Role Selection Permission
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

            // Approval Status
            'approval_status' => [
                'required',
                Rule::in([
                    'pending',
                    'approved',
                    'rejected',
                ]),
            ],

            // Member Status
            'member_status' => [
                'nullable',
                Rule::in(User::MEMBER_STATUSES),
            ],

            // Membership Type
            'member_type' => [
                'nullable',

                Rule::requiredIf(
                    fn () => $memberStatus === User::MEMBER_STATUS_MEMBER
                ),

                Rule::prohibitedIf(
                    fn () => $memberStatus !== User::MEMBER_STATUS_MEMBER
                ),

                Rule::in(User::MEMBER_TYPES),
            ],

            // Chamber Room Number
            'chamber_room_no' => [
                'nullable',
                'string',
                'max:50',
                'required_with:chamber_type',

                Rule::prohibitedIf(
                    fn () => $memberStatus !== User::MEMBER_STATUS_MEMBER
                ),
            ],

            // Chamber Type
            'chamber_type' => [
                'nullable',
                Rule::in(User::CHAMBER_TYPES),
                'required_with:chamber_room_no',

                Rule::prohibitedIf(
                    fn () => $memberStatus !== User::MEMBER_STATUS_MEMBER
                ),
            ],

            // Lawyer Relationship
            'lawyer_id' => [
                'nullable',
                'integer',

                // Required for lawyer assistants.
                Rule::requiredIf(
                    fn () => $memberStatus
                        === User::MEMBER_STATUS_LAWYER_ASSISTANT
                ),

                // Only apprentices and lawyer assistants can have a lawyer.
                Rule::prohibitedIf(
                    fn () => !in_array(
                        $memberStatus,
                        [
                            User::MEMBER_STATUS_LAWYER_ASSISTANT,
                            User::MEMBER_STATUS_APPRENTICE,
                        ],
                        true
                    )
                ),

                // Only actual members can be assigned as lawyers.
                Rule::exists('users', 'id')
                    ->whereIn('member_status', [
                        User::MEMBER_STATUS_MEMBER,
                    ]),
            ],
        ];
    }

    /**
     * Configure additional validation rules.
     */
    public function after(): array
    {
        return [
            function (Validator $validator): void {

                // Approval Permission Check
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

                // Member Status Permission Check
                if (
                    $this->exists('member_status')
                    && !$this->user()?->hasPermission('users.change_status')
                    && $this->input('member_status')
                        !== User::MEMBER_STATUS_MEMBER
                ) {
                    $validator->errors()->add(
                        'member_status',
                        'You do not have permission to set this member status.'
                    );
                }
            },
        ];
    }

    /**
     * Custom validation error messages.
     */
    public function messages(): array
    {
        return [

            // Basic Information
            'full_name.required' => 'The full name is required.',
            'mobile.required' => 'A mobile number is required.',
            'mobile.unique' => 'This mobile number is already registered.',
            'email.unique' => 'This email address is already registered.',

            // Password
            'password.confirmed' => 'The password confirmation does not match.',

            // Approval
            'approval_status.in' => 'Please select a valid approval status.',

            // Member Status
            'member_status.in' => 'Please select a valid member status.',

            // Membership Type
            'member_type.required' => 'Please select a membership type.',
            'member_type.in' => 'Please select a valid membership type.',
            'member_type.prohibited' => 'Membership type is only applicable to members.',

            // Chamber Allocation
            'chamber_room_no.required_with' => 'Chamber room number is required.',
            'chamber_room_no.prohibited' => 'Only members can have chamber allocation.',
            'chamber_type.required_with' => 'Chamber type is required.',
            'chamber_type.in' => 'Please select a valid chamber type.',
            'chamber_type.prohibited' => 'Only members can have chamber allocation.',

            // Lawyer Relationship
            'lawyer_id.required' => 'Please select a lawyer for this lawyer assistant.',
            'lawyer_id.exists' => 'The selected lawyer is invalid or is not an eligible member.',
            'lawyer_id.prohibited' => 'This user cannot have an assigned lawyer.',

            // Roles
            'roles.array' => 'The roles field must be an array.',
            'roles.*.integer' => 'Each selected role must be valid.',
            'roles.*.distinct' => 'A role cannot be selected more than once.',
            'roles.*.exists' => 'One or more selected roles do not exist.',

            // NID Images
            'nid_images.front.mimes' => 'The front NID image must be JPG or PNG.',
            'nid_images.back.mimes' => 'The back NID image must be JPG or PNG.',
        ];
    }
}
