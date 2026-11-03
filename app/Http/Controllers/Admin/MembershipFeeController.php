<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreSettingRequest;
use App\Models\SystemSetting;
use App\Services\Membership\MembershipFeeService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class MembershipFeeController extends Controller
{
    public function index(): View
    {
        $settings = SystemSetting::query()
            ->where('group', 'membership')
            ->pluck('value', 'key');

        return view('admin.membership-fees.index', compact('settings'));
    }

    public function update(
        StoreSettingRequest $request,
        MembershipFeeService $feeService
    ): RedirectResponse {
        $data = $request->validated();

        DB::transaction(function () use ($data) {
            $settings = [
                'membership.registration_fee' => $data['registration_fee'],
                'membership.yearly_fee.member' => $data['yearly_fee_member'],
                'membership.yearly_fee.life_member' => $data['yearly_fee_life_member'],
            ];

            foreach ($settings as $key => $value) {
                SystemSetting::updateOrCreate(
                    ['key' => $key],
                    [
                        'value' => $value,
                        'type' => 'text',
                        'group' => 'membership',
                    ]
                );
            }
        });

        $feeService->clearCache();

        return back()->with('success', 'Membership fees updated successfully.');
    }
}