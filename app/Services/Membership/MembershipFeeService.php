<?php

namespace App\Services\Membership;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\SystemSetting;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MembershipFeeService
{
    private const CACHE_KEY = 'membership.fee.settings';

    private const CACHE_TTL = 86400;

    private const MONTHLY_INVOICE_TYPE = 'monthly_membership';

    private const YEARLY_INVOICE_TYPE = 'yearly_membership';

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

    private const YEARLY_FEE_KEYS = [
        'membership.recurring.annual_building_fund',
        'membership.recurring.annual_library_fee',
        'membership.recurring.annual_library_card_fee',
        'membership.recurring.annual_relief_fund',
        'membership.recurring.annual_education_fund',
        'membership.recurring.annual_retirement_fund',
        'membership.recurring.annual_mb_fund',
    ];

    /**
     * Return all configured fee settings.
     */
    public function fees(): array
    {
        return Cache::remember(
            self::CACHE_KEY,
            self::CACHE_TTL,
            function (): array {
                $stored = SystemSetting::query()
                    ->whereIn('key', array_keys(self::DEFAULT_FEES))
                    ->pluck('value', 'key')
                    ->all();

                $fees = [];

                foreach (self::DEFAULT_FEES as $key => $default) {
                    $fees[$key] = round(
                        (float) ($stored[$key] ?? $default),
                        2
                    );
                }

                return $fees;
            }
        );
    }

    /**
     * Get one fee amount.
     */
    public function fee(string $key): float
    {
        $fees = $this->fees();

        if (! array_key_exists($key, $fees)) {
            throw new \InvalidArgumentException(
                "Unknown membership fee: {$key}"
            );
        }

        return $fees[$key];
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    /**
     * Determine the first billing month from bar_join_date.
     *
     * Joining on day 1-15: current month.
     * Joining on day 16 onwards: next month.
     */
    public function billingStartDate(User $user): ?CarbonImmutable
    {
        if (! $user->bar_join_date) {
            return null;
        }

        $joiningDate = CarbonImmutable::parse(
            $user->bar_join_date
        );

        $monthStart = $joiningDate->startOfMonth();

        return $joiningDate->day >= 16
            ? $monthStart->addMonth()
            : $monthStart;
    }

    /**
     * Create one monthly invoice containing monthly membership
     * and applicable chamber rent.
     */
    public function createMonthlyInvoice(
        User $user,
        CarbonImmutable $periodStart,
        ?int $createdBy = null
    ): ?Invoice {
        if (! $user->isMember()) {
            return null;
        }

        $periodStart = $periodStart->startOfMonth();
        $periodEnd = $periodStart->endOfMonth();

        $billingStart = $this->billingStartDate($user);

        if (! $billingStart || $periodStart->lt($billingStart)) {
            return null;
        }

        return DB::transaction(function () use (
            $user,
            $periodStart,
            $periodEnd,
            $createdBy
        ) {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            if (! $lockedUser->isMember()) {
                return null;
            }

            $cycleKey = $periodStart->format('Y-m');

            $invoice = Invoice::query()
                ->where('user_id', $lockedUser->id)
                ->where('invoice_type', self::MONTHLY_INVOICE_TYPE)
                ->where('cycle_key', $cycleKey)
                ->first();

            if ($invoice) {
                return $invoice;
            }

            $items = [];

            $items[] = [
                'fee_key' => 'monthly_membership',
                'title' => 'Monthly Membership Fee',
                'description' => 'Monthly membership subscription',
                'amount' => $this->fee(
                    'membership.recurring.monthly_membership'
                ),
                'metadata' => [],
            ];

            $chamberFee = $this->calculateChamberMonthlyFee(
                $lockedUser,
                $periodStart
            );

            if ($chamberFee !== null) {
                $items[] = $chamberFee;
            }

            return $this->createInvoiceWithItems(
                user: $lockedUser,
                invoiceType: self::MONTHLY_INVOICE_TYPE,
                cycleKey: $cycleKey,
                title: 'Monthly Membership Invoice - '.$cycleKey,
                description: 'Monthly membership and chamber charges',
                periodStart: $periodStart,
                periodEnd: $periodEnd,
                items: $items,
                createdBy: $createdBy
            );
        }, 3);
    }

    /**
     * Calculate chamber rent for one billing month.
     */
    private function calculateChamberMonthlyFee(
        User $user,
        CarbonImmutable $periodStart
    ): ?array {
        if (! $user->hasChamberAllocation()) {
            return null;
        }

        $billingStart = $this->billingStartDate($user);

        if (! $billingStart) {
            return null;
        }

        $feeKey = null;
        $amount = 0;

        switch ($user->chamber_type) {
            case User::CHAMBER_TYPE_STANDARD:
                $feeKey = 'chamber.rent.standard_monthly';
                $amount = $this->fee($feeKey);
                break;

            case User::CHAMBER_TYPE_JOINT:
                $feeKey = 'chamber.rent.joint_monthly';
                $amount = $this->fee($feeKey);
                break;

            case User::CHAMBER_TYPE_AC:
                $monthsElapsed = $billingStart->diffInMonths(
                    $periodStart
                );

                $feeKey = $monthsElapsed < 6
                    ? 'chamber.rent.ac.first_six_months'
                    : 'chamber.rent.ac.remaining_six_months';

                $amount = $this->fee($feeKey);
                break;

            default:
                return null;
        }

        return [
            'fee_key' => 'chamber_rent',
            'title' => 'Chamber Rent - '.$user->chamber_type_label,
            'description' => 'Room: '.$user->chamber_room_no,
            'amount' => $amount,
            'metadata' => [
                'chamber_type' => $user->chamber_type,
                'chamber_room_no' => $user->chamber_room_no,
                'configuration_fee_key' => $feeKey,
                'billing_month' => $periodStart->format('Y-m'),
                'months_elapsed' => $user->chamber_type === User::CHAMBER_TYPE_AC
                    ? $monthsElapsed
                    : null,
            ],
        ];
    }

    /**
     * Create one yearly invoice containing all annual fee items.
     *
     * First annual invoice is generated after the first
     * billing year has completed.
     */
    public function createYearlyInvoice(
        User $user,
        int $year,
        ?int $createdBy = null
    ): ?Invoice {
        if (! $user->isMember() || $year < 2000 || $year > 2100) {
            return null;
        }

        $billingStart = $this->billingStartDate($user);

        if (! $billingStart || $year <= $billingStart->year) {
            return null;
        }

        $periodStart = CarbonImmutable::create(
            $year,
            $billingStart->month,
            1
        );

        if ($periodStart->isFuture()) {
            return null;
        }

        $periodEnd = $periodStart->addYear()->subDay();

        return DB::transaction(function () use (
            $user,
            $year,
            $periodStart,
            $periodEnd,
            $createdBy
        ) {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            if (! $lockedUser->isMember()) {
                return null;
            }

            $cycleKey = (string) $year;

            $invoice = Invoice::query()
                ->where('user_id', $lockedUser->id)
                ->where('invoice_type', self::YEARLY_INVOICE_TYPE)
                ->where('cycle_key', $cycleKey)
                ->first();

            if ($invoice) {
                return $invoice;
            }

            $items = [];

            foreach (self::YEARLY_FEE_KEYS as $feeKey) {
                $amount = $this->fee($feeKey);

                $items[] = [
                    'fee_key' => str_replace(
                        'membership.recurring.',
                        '',
                        $feeKey
                    ),
                    'title' => $this->yearlyFeeTitle($feeKey),
                    'description' => 'Annual membership fee',
                    'amount' => $amount,
                    'metadata' => [
                        'configuration_fee_key' => $feeKey,
                    ],
                ];
            }

            return $this->createInvoiceWithItems(
                user: $lockedUser,
                invoiceType: self::YEARLY_INVOICE_TYPE,
                cycleKey: $cycleKey,
                title: 'Annual Membership Invoice - '.$year,
                description: 'Annual membership fees',
                periodStart: $periodStart,
                periodEnd: $periodEnd,
                items: $items,
                createdBy: $createdBy
            );
        }, 3);
    }

    /**
     * Create invoice and its item snapshots atomically.
     */
    private function createInvoiceWithItems(
        User $user,
        string $invoiceType,
        string $cycleKey,
        string $title,
        string $description,
        CarbonImmutable $periodStart,
        CarbonImmutable $periodEnd,
        array $items,
        ?int $createdBy
    ): Invoice {
        if (empty($items)) {
            throw ValidationException::withMessages([
                'invoice' => 'An invoice must contain at least one fee item.',
            ]);
        }

        $total = collect($items)->sum(
            fn (array $item) => (float) $item['amount']
        );

        if ($total <= 0) {
            throw ValidationException::withMessages([
                'invoice' => 'Invoice total must be greater than zero.',
            ]);
        }

        $invoice = new Invoice();

        $invoice->forceFill([
            'user_id' => $user->id,
            'invoice_number' => $this->generateInvoiceNumber(),
            'invoice_type' => $invoiceType,
            'cycle_key' => $cycleKey,
            'member_status' => $user->member_status,
            'title' => $title,
            'description' => $description,
            'amount' => $total,
            'status' => 'pending',
            'due_date' => now()->addDays(30)->toDateString(),
            'created_by' => $createdBy,
        ]);

        $invoice->save();

        foreach ($items as $item) {
            InvoiceItem::query()->create([
                'invoice_id' => $invoice->id,
                'fee_key' => $item['fee_key'],
                'title' => $item['title'],
                'description' => $item['description'],
                'unit_amount' => $item['amount'],
                'quantity' => 1,
                'amount' => $item['amount'],
                'period_start' => $periodStart->toDateString(),
                'period_end' => $periodEnd->toDateString(),
                'metadata' => $item['metadata'] ?? [],
            ]);
        }

        return $invoice->load('items');
    }

    private function yearlyFeeTitle(string $key): string
    {
        return match ($key) {
            'membership.recurring.annual_building_fund' => 'Annual Building Fund',
            'membership.recurring.annual_library_fee' => 'Annual Library Fee',
            'membership.recurring.annual_library_card_fee' => 'Annual Library Card Fee',
            'membership.recurring.annual_relief_fund' => 'Annual Relief Fund',
            'membership.recurring.annual_education_fund' => 'Annual Education Fund',
            'membership.recurring.annual_retirement_fund' => 'Annual Retirement Fund',
            'membership.recurring.annual_mb_fund' => 'Annual M.B. Fund',
            default => 'Annual Membership Fee',
        };
    }

    /**
     * Generate a unique invoice number.
     */
    private function generateInvoiceNumber(): string
    {
        do {
            $number = 'KDBA-'.now()->format('Ymd').'-'.strtoupper(
                bin2hex(random_bytes(4))
            );
        } while (
            Invoice::query()
                ->where('invoice_number', $number)
                ->exists()
        );

        return $number;
    }

    /**
     * Admission fee for mother/sub member.
     */
    public function admissionFee(string $memberType): float
    {
        $key = match ($memberType) {
            User::MEMBER_TYPE_MOTHER => 'membership.admission.mother_member',
            User::MEMBER_TYPE_SUB => 'membership.admission.sub_member',
            default => throw new \InvalidArgumentException(
                'Invalid member type.'
            ),
        };

        return $this->fee($key);
    }


    public function createRegistrationInvoice(
        User $user,
        ?int $createdBy = null
    ): ?Invoice {
        if (! $user->isMember()) {
            return null;
        }

        if (! in_array($user->member_type, User::MEMBER_TYPES, true)) {
            throw ValidationException::withMessages([
                'member_type' => 'A valid membership type is required.',
            ]);
        }

        return DB::transaction(function () use ($user, $createdBy) {
            $lockedUser = User::query()
                ->lockForUpdate()
                ->findOrFail($user->id);

            if (! $lockedUser->isMember()) {
                return null;
            }

            $amount = $this->admissionFee($lockedUser->member_type);

            $invoice = Invoice::query()
                ->where('user_id', $lockedUser->id)
                ->where('invoice_type', 'registration')
                ->where('cycle_key', 'registration')
                ->first();

            if (! $invoice) {
                $invoice = new Invoice();

                $invoice->forceFill([
                    'user_id' => $lockedUser->id,
                    'invoice_number' => $this->generateInvoiceNumber(),
                    'invoice_type' => 'registration',
                    'cycle_key' => 'registration',
                    'member_status' => $lockedUser->member_status,
                    'title' => 'Initial Membership Admission Fee',
                    'description' => 'Initial admission fee for '.$lockedUser->member_type,
                    'amount' => $amount,
                    'status' => 'pending',
                    'due_date' => now()->addDays(30)->toDateString(),
                    'created_by' => $createdBy,
                ]);

                $invoice->save();
            }

            // Never duplicate an existing admission fee item.
            if (! $invoice->items()->exists()) {
                $invoice->items()->create([
                    'fee_key' => 'admission_fee',
                    'title' => 'Membership Admission Fee',
                    'description' => ucfirst(str_replace(
                        '_',
                        ' ',
                        $lockedUser->member_type
                    )),
                    'unit_amount' => $amount,
                    'quantity' => 1,
                    'amount' => $amount,
                    'metadata' => [
                        'member_type' => $lockedUser->member_type,
                    ],
                ]);
            }

            return $invoice->load('items');
        }, 3);
    }

    
    public function createPaidInvoiceItemLedger(
        Invoice $invoice,
        ?int $recordedBy = null
    ): int {
        return DB::transaction(function () use ($invoice, $recordedBy) {
            $lockedInvoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            // Only fully paid invoices can generate ledger entries.
            if ($lockedInvoice->status !== 'paid') {
                return 0;
            }

            $lockedInvoice->load('items');

            $created = 0;
            $now = now();

            foreach ($lockedInvoice->items as $item) {
                // Skip an item that already has a ledger transaction.
                $exists = Transaction::query()
                    ->where('invoice_item_id', $item->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                try {
                    /*
                    * Use a nested transaction/savepoint.
                    * This allows us to safely catch a duplicate-key exception
                    * without breaking the surrounding payment transaction.
                    */
                    $wasCreated = DB::transaction(function () use (
                        $lockedInvoice,
                        $item,
                        $recordedBy,
                        $now
                    ) {
                        Transaction::query()->create([
                            'invoice_id' => $lockedInvoice->id,
                            'invoice_item_id' => $item->id,
                            'user_id' => $lockedInvoice->user_id,
                            'category_id' => null,
                            'payment_gateway_id' => null,
                            'trx_id' => 'LEDGER-'.$lockedInvoice->invoice_number.'-'.$item->id,
                            'amount' => $item->amount,
                            'status' => 'approved',
                            'source' => 'system',
                            'recorded_by' => $recordedBy,
                            'verified_by' => $recordedBy,
                            'verified_at' => $now,
                            'submitted_at' => $now,
                        ]);

                        return true;
                    }, 3);

                    if ($wasCreated) {
                        $created++;
                    }
                } catch (QueryException $exception) {
                    // Ignore only duplicate invoice_item_id violations.
                    if ($this->isDuplicateInvoiceItemLedger($exception)) {
                        continue;
                    }

                    // Do not hide unrelated database errors.
                    throw $exception;
                }
            }

            return $created;
        }, 3);
    }

    private function isDuplicateInvoiceItemLedger(
        QueryException $exception
    ): bool {
        $errorInfo = $exception->errorInfo ?? [];

        $sqlState = (string) ($errorInfo[0] ?? '');
        $driverCode = (int) ($errorInfo[1] ?? 0);
        $message = strtolower($exception->getMessage());

        $isUniqueViolation =
            $sqlState === '23505' // PostgreSQL
            || ($sqlState === '23000' && $driverCode === 1062) // MySQL
            || ($sqlState === '23000' && $driverCode === 19); // SQLite

        if (! $isUniqueViolation) {
            return false;
        }

        return str_contains($message, 'transactions_invoice_item_unique')
            || str_contains($message, 'invoice_item_id');
    }


}
