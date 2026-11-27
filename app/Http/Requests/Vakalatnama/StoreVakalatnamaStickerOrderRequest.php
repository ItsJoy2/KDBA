<?php

namespace App\Http\Requests\Vakalatnama;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class StoreVakalatnamaStickerOrderRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'user_id' => ['nullable', 'integer','exists:users,id',
            ],
            'quantity' => ['required','integer', 'min:1', 'max:500',],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            $actor = $this->user();

            if (! $actor) {
                return;
            }

            $isAdminRoute = $this->routeIs(
                'admin.vakalatnama.*'
            );

            if ($isAdminRoute) {
                if (! $actor->can('vakalatnama.create')) {
                    $validator->errors()->add(
                        'permission',
                        'You are not authorized to create sticker orders.'
                    );

                    return;
                }

                if (! $this->filled('user_id')) {
                    $validator->errors()->add(
                        'user_id',
                        'Please select a member.'
                    );

                    return;
                }

                $member = User::find($this->input('user_id'));
            } else {
                // Member orders always belong to the authenticated user.
                if ($this->filled('user_id')) {
                    $validator->errors()->add(
                        'user_id',
                        'Members cannot select another account.'
                    );

                    return;
                }

                $member = $actor;
            }

            if (! $member) {
                return;
            }

            $eligible = $member->approval_status === 'approved'
                && in_array(
                    $member->member_status,
                    ['active', 'member'],
                    true
                );

            if (! $eligible) {
                $validator->errors()->add(
                    'user_id',
                    'The selected user is not an eligible member.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'quantity.required' => 'Please enter sticker quantity.',
            'quantity.min' => 'At least one sticker is required.',
            'quantity.max' => 'Maximum order quantity is 500.',
            'user_id.exists' => 'The selected member does not exist.',
        ];
    }
}
