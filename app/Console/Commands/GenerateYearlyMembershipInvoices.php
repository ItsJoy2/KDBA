<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Membership\MembershipFeeService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateYearlyMembershipInvoices extends Command
{
    protected $signature = 'membership:generate-yearly-invoices
                            {--year= : Billing year (defaults to current year)}';

    protected $description = 'Generate yearly membership invoices';

    public function handle(MembershipFeeService $feeService): int
    {
        $yearOption = $this->option('year');

        $year = $yearOption !== null
            ? filter_var($yearOption, FILTER_VALIDATE_INT)
            : (int) now()->year;

        if (
            $year === false ||
            $year < 2000 ||
            $year > 2100
        ) {
            $this->error('Invalid billing year.');

            return self::FAILURE;
        }

        if ($year > now()->year) {
            $this->error('Future billing years are not allowed.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $this->info("Generating yearly membership invoices for {$year}...");

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
                $year,
                &$created,
                &$skipped,
                &$failed
            ) {
                foreach ($users as $user) {
                    try {
                        $invoice = $feeService->createYearlyInvoice(
                            $user,
                            $year
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

                        Log::error('Yearly membership invoice generation failed.', [
                            'user_id' => $user->id,
                            'year' => $year,
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
