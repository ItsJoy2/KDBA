<?php

namespace App\Services\Membership;

use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MembershipFeeService
{
    private const CACHE_KEY = 'membership.fee.settings';

    /**
     * Default fee configuration.
     */
    private const DEFAULT_FEES = [
        'membership.admission.mother_member' => 50000,
        'membership.admission.sub_member' => 100000,

        'membership.recurring.monthly_membership' => 100,

        'membership.recurring.annual_building_fund' => 100,
        'membership.recurring.annual_library_fee' => 100,
        'membership.recurring.annual_library_card_fee' => 100,
        'membership.recurring.annual_relief_fund' => 100,
        'membership.recurring.annual_education_fund' => 100,
        'membership.recurring.annual_retirement_fund' => 100,
        'membership.recurring.annual_mb_fund' => 2000,

        'apprentice.admission_fee' => 1000,
        'apprentice.card_fee' => 500,

        'lawyer_assistant.card_renewal_fee' => 200,

        'chamber.rent.standard_monthly' => 500,
        'chamber.rent.joint_monthly' => 500,
        'chamber.rent.ac.first_six_months' => 500,
        'chamber.rent.ac.remaining_six_months' => 1000,
    ];

    /**
     * Get all fee settings.
     */
    public function fees(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addHours(24),
            function (): array {
                $storedFees = SystemSetting::query()
                    ->whereIn('key', array_keys(self::DEFAULT_FEES))
                    ->pluck('value', 'key')
                    ->toArray();

                $fees = [];

                foreach (self::DEFAULT_FEES as $key => $default) {
                    $fees[$key] = (float) (
                        $storedFees[$key] ?? $default
                    );
                }

                // Backward compatibility for old registration fee.
                if (!isset($storedFees['membership.admission.mother_member'])) {
                    $legacyRegistrationFee = SystemSetting::query()
                        ->where('key', 'membership.registration_fee')
                        ->value('value');

                    if ($legacyRegistrationFee !== null) {
                        $fees['membership.admission.mother_member'] =
                            (float) $legacyRegistrationFee;
                    }
                }

                return $fees;
            }
        );
    }

    /**
     * Get a single fee.
     */
    public function fee(string $key): float
    {
        $fees = $this->fees();

        if (!array_key_exists($key, $fees)) {
            throw new InvalidArgumentException(
                "Unknown fee configuration: {$key}"
            );
        }

        return $fees[$key];
    }

    /**
     * Get admission fee according to member type.
     */
    public function admissionFee(string $memberType): float
    {
        return match ($memberType) {
            User::MEMBER_TYPE_MOTHER => $this->fee(
                'membership.admission.mother_member'
            ),
            User::MEMBER_TYPE_SUB => $this->fee(
                'membership.admission.sub_member'
            ),
            default => throw new InvalidArgumentException(
                'Invalid membership type.'
            ),
        };
    }

    /**
     * Backward-compatible registration fee method.
     */
    public function registrationFee(
        ?string $memberType = null
    ): float {
        return $memberType
            ? $this->admissionFee($memberType)
            : $this->fee('membership.admission.mother_member');
    }

    /**
     * Calculate yearly membership invoice amount.
     *
     * Includes 12 months of membership fee and all annual funds.
     */
    public function yearlyFee(string $memberStatus): float
    {
        if ($memberStatus !== User::MEMBER_STATUS_MEMBER) {
            throw new InvalidArgumentException(
                'Invalid membership status for yearly fee.'
            );
        }

        $annualKeys = [
            'membership.recurring.annual_building_fund',
            'membership.recurring.annual_library_fee',
            'membership.recurring.annual_library_card_fee',
            'membership.recurring.annual_relief_fund',
            'membership.recurring.annual_education_fund',
            'membership.recurring.annual_retirement_fund',
            'membership.recurring.annual_mb_fund',
        ];

        $annualTotal = array_sum(
            array_map(
                fn (string $key) => $this->fee($key),
                $annualKeys
            )
        );

        $monthlyTotal = $this->fee(
            'membership.recurring.monthly_membership'
        ) * 12;

        return $monthlyTotal + $annualTotal;
    }

    /**
     * Get apprentice admission and card fees.
     */
    public function apprenticeFees(): array
    {
        return [
            'admission' => $this->fee('apprentice.admission_fee'),
            'card' => $this->fee('apprentice.card_fee'),
        ];
    }

    /**
     * Get lawyer assistant card renewal fee.
     */
    public function lawyerAssistantCardRenewalFee(): float
    {
        return $this->fee('lawyer_assistant.card_renewal_fee');
    }

    /**
     * Get monthly chamber rent.
     */
    public function chamberMonthlyFee(string $chamberType): float
    {
        return match ($chamberType) {
            User::CHAMBER_TYPE_STANDARD => $this->fee(
                'chamber.rent.standard_monthly'
            ),
            User::CHAMBER_TYPE_JOINT => $this->fee(
                'chamber.rent.joint_monthly'
            ),
            User::CHAMBER_TYPE_AC => $this->fee(
                'chamber.rent.ac.first_six_months'
            ),
            default => throw new InvalidArgumentException(
                'Invalid chamber type.'
            ),
        };
    }

    /**
     * Calculate AC chamber annual rent per person.
     */
    public function chamberAcAnnualFee(): float
    {
        return (
            $this->fee('chamber.rent.ac.first_six_months') * 6
        ) + (
            $this->fee('chamber.rent.ac.remaining_six_months') * 6
        );
    }

    /**
     * Clear cached fee settings.
     */
    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Generate the initial admission invoice.
     */
    public function createRegistrationInvoice(
        User $user,
        ?int $createdBy = null
    ): ?Invoice {
        if (
            $user->member_status !== User::MEMBER_STATUS_MEMBER
            || !in_array($user->member_type, User::MEMBER_TYPES, true)
        ) {
            return null;
        }

        $amount = $this->admissionFee($user->member_type);

        if ($amount <= 0) {
            return null;
        }

        return DB::transaction(function () use ($user, $amount, $createdBy) {
            $lockedUser = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            if (
                $lockedUser->member_status !== User::MEMBER_STATUS_MEMBER
                || !in_array(
                    $lockedUser->member_type,
                    User::MEMBER_TYPES,
                    true
                )
            ) {
                return null;
            }

            return Invoice::firstOrCreate(
                [
                    'user_id' => $lockedUser->id,
                    'invoice_type' => 'registration',
                    'cycle_key' => 'registration',
                ],
                [
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'member_status' => $lockedUser->member_status,
                    'title' => 'Initial Membership Admission Fee',
                    'description' => sprintf(
                        'Initial admission fee for %s.',
                        str_replace('_', ' ', $lockedUser->member_type)
                    ),
                    'amount' => $amount,
                    'status' => 'pending',
                    'due_date' => now()->addDays(30)->toDateString(),
                    'created_by' => $createdBy ?? $lockedUser->created_by,
                ]
            );
        });
    }

    /**
     * Generate yearly membership invoice.
     */
    public function createYearlyInvoice(
        User $user,
        int $year
    ): ?Invoice {
        if ($user->member_status !== User::MEMBER_STATUS_MEMBER) {
            return null;
        }

        $amount = $this->yearlyFee($user->member_status);

        if ($amount <= 0) {
            return null;
        }

        return Invoice::firstOrCreate(
            [
                'user_id' => $user->id,
                'invoice_type' => 'yearly_renewal',
                'cycle_key' => (string) $year,
            ],
            [
                'invoice_number' => $this->generateInvoiceNumber(),
                'member_status' => $user->member_status,
                'title' => "Yearly Membership Renewal Fee - {$year}",
                'description' => "Membership and annual funds for {$year}.",
                'amount' => $amount,
                'status' => 'pending',
                'due_date' => "{$year}-03-31",
            ]
        );
    }

    /**
     * Generate unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        return 'KDBA-' . now()->format('Ymd') . '-' . Str::upper(
            Str::random(8)
        );
    }
}
