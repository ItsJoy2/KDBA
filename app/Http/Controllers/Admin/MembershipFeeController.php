<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\Membership\MembershipFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MembershipFeeController extends Controller
{
    /**
     * Available fee configuration.
     */
    private const FEE_FIELDS = [
        'membership.admission.mother_member',
        'membership.admission.sub_member',

        'membership.recurring.monthly_membership',

        'membership.recurring.annual_building_fund',
        'membership.recurring.annual_library_fee',
        'membership.recurring.annual_library_card_fee',
        'membership.recurring.annual_relief_fund',
        'membership.recurring.annual_education_fund',
        'membership.recurring.annual_retirement_fund',
        'membership.recurring.annual_mb_fund',

        'apprentice.admission_fee',
        'apprentice.card_fee',

        'lawyer_assistant.card_renewal_fee',

        'chamber.rent.standard_monthly',
        'chamber.rent.joint_monthly',
        'chamber.rent.ac.first_six_months',
        'chamber.rent.ac.remaining_six_months',
    ];

    /**
     * Display fee settings.
     */
    public function index(): View
    {
        abort_unless(
            auth()->user()?->hasPermission('fees.manage'),
            403
        );

        $settings = SystemSetting::query()
            ->whereIn('key', self::FEE_FIELDS)
            ->pluck('value', 'key');

        return view(
            'admin.membership-fees.index',
            compact('settings')
        );
    }

    /**
     * Update fee settings.
     */
    public function update(
        Request $request,
        MembershipFeeService $feeService
    ): RedirectResponse {
        abort_unless(
            $request->user()?->hasPermission('fees.manage'),
            403
        );

        $rules = [];

        foreach (self::FEE_FIELDS as $key) {
            $rules["fees.{$key}"] = [
                'required',
                'numeric',
                'min:0',
                'max:999999999.99',
            ];
        }

        $validated = $request->validate($rules);

        $fees = $validated['fees'] ?? [];

        if (count($fees) !== count(self::FEE_FIELDS)) {
            throw ValidationException::withMessages([
                'fees' => 'All fee fields are required.',
            ]);
        }

        DB::transaction(function () use ($fees) {
            foreach (self::FEE_FIELDS as $key) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => number_format(
                            (float) $fees[$key],
                            2,
                            '.',
                            ''
                        ),
                        'type' => 'text',
                        'group' => str_starts_with($key, 'membership.')
                            ? 'membership'
                            : (
                                str_starts_with($key, 'apprentice.')
                                    ? 'apprentice'
                                    : (
                                        str_starts_with($key, 'lawyer_assistant.')
                                            ? 'lawyer_assistant'
                                            : 'chamber'
                                    )
                            ),
                    ]
                );
            }
        });

        $feeService->clearCache();

        return back()->with(
            'success',
            'All fee settings updated successfully.'
        );
    }
}
