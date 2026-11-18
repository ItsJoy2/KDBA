<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Membership\MembershipFeeService;
use Carbon\CarbonImmutable;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateMonthlyMembershipInvoices extends Command
{
    protected $signature = 'membership:generate-monthly-invoices
                            {--month= : Billing month in YYYY-MM format}';

    protected $description = 'Generate monthly membership and chamber invoices';

    public function handle(MembershipFeeService $feeService): int
    {
        $monthOption = $this->option('month');

        // Prevent accidental early generation during normal scheduler runs.
        if (! $monthOption && ! now()->isLastOfMonth()) {
            $this->info('Monthly invoices are generated on the last day of the month.');

            return self::SUCCESS;
        }

        $month = $monthOption ?: now()->format('Y-m');

        if (! preg_match('/^\d{4}-(0[1-9]|1[0-2])$/', $month)) {
            $this->error('Invalid month. Expected format: YYYY-MM.');

            return self::FAILURE;
        }

        try {
            $periodStart = CarbonImmutable::createFromFormat(
                '!Y-m',
                $month
            );
        } catch (Throwable $exception) {
            $this->error('Invalid billing month.');

            return self::FAILURE;
        }

        if (! $periodStart || $periodStart->format('Y-m') !== $month) {
            $this->error('Invalid billing month.');

            return self::FAILURE;
        }

        if ($periodStart->greaterThan(now()->startOfMonth())) {
            $this->error('Future billing months are not allowed.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $this->info("Generating monthly invoices for {$month}...");

        User::query()
            ->members()
            ->select([
                'id',
                'member_status',
                'member_type',
                'bar_join_date',
                'chamber_type',
                'chamber_room_no',
            ])
            ->chunkById(200, function ($users) use (
                $feeService,
                $periodStart,
                &$created,
                &$skipped,
                &$failed
            ) {
                foreach ($users as $user) {
                    try {
                        $invoice = $feeService->createMonthlyInvoice(
                            $user,
                            $periodStart
                        );

                        if (! $invoice) {
                            $skipped++;
                            continue;
                        }

                        if ($invoice->wasRecentlyCreated) {
                            $created++;
                        } else {
                            $skipped++;
                        }
                    } catch (Throwable $exception) {
                        $failed++;

                        Log::error('Monthly membership invoice generation failed.', [
                            'user_id' => $user->id,
                            'month' => $periodStart->format('Y-m'),
                            'exception' => $exception->getMessage(),
                        ]);

                        $this->warn(
                            "Failed for user ID {$user->id}."
                        );
                    }
                }
            });

        $this->newLine();
        $this->info("Created: {$created}");
        $this->info("Skipped: {$skipped}");
        $this->info("Failed: {$failed}");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
