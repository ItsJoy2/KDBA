<?php

namespace App\Services\Membership;

use App\Models\Invoice;
use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;
use InvalidArgumentException;

class MembershipFeeService
{
    private const CACHE_KEY = 'membership.fee.settings';

    public function fees(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            now()->addHours(24),
            function () {
                return SystemSetting::query()
                    ->whereIn('key', [
                        'membership.registration_fee',
                        'membership.yearly_fee.member',
                        'membership.yearly_fee.life_member',
                    ])
                    ->pluck('value', 'key')
                    ->toArray();
            }
        );
    }

    public function registrationFee(): float
    {
        return (float) ($this->fees()['membership.registration_fee'] ?? 0);
    }

    public function yearlyFee(string $memberStatus): float
    {
        return match ($memberStatus) {
            'member' => (float) (
                $this->fees()['membership.yearly_fee.member'] ?? 0
            ),
            'life_member' => (float) (
                $this->fees()['membership.yearly_fee.life_member'] ?? 0
            ),
            default => throw new InvalidArgumentException(
                'Invalid membership status for yearly fee.'
            ),
        };
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Generate the initial registration invoice for an eligible user.
     */
    public function createRegistrationInvoice(User $user): ?Invoice
    {
        if (! in_array($user->member_status, ['member', 'life_member'], true)) {
            return null;
        }

        $amount = $this->registrationFee();

        if ($amount <= 0) {
            return null;
        }

        return Invoice::firstOrCreate(
            [
                'user_id' => $user->id,
                'invoice_type' => 'registration',
                'cycle_key' => 'registration',
            ],
            [
                'invoice_number' => $this->generateInvoiceNumber(),
                'member_status' => $user->member_status,
                'title' => 'Initial Membership Registration Fee',
                'description' => 'Initial membership registration fee.',
                'amount' => $amount,
                'status' => 'pending',
                'due_date' => now()->addDays(30)->toDateString(),
            ]
        );
    }

    /**
     * Generate a yearly renewal invoice.
     */
    public function createYearlyInvoice(
        User $user,
        int $year
    ): ?Invoice {
        if (! in_array($user->member_status, ['member', 'life_member'], true)) {
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
                'description' => "Membership renewal fee for {$year}.",
                'amount' => $amount,
                'status' => 'pending',
                'due_date' => "{$year}-03-31",
            ]
        );
    }

    private function generateInvoiceNumber(): string
    {
        return 'KDBA-' . now()->format('Ymd') . '-' . Str::upper(
            Str::random(8)
        );
    }
}