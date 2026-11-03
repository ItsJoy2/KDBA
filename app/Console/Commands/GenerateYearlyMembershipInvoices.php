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
                            {--year= : The renewal year (defaults to current year)}';

    protected $description = 'Generate yearly membership renewal invoices';

    public function handle(MembershipFeeService $feeService): int
    {
        $year = (int) ($this->option('year') ?: now()->year);

        if ($year < 2000 || $year > 2100) {
            $this->error('Invalid renewal year.');

            return self::FAILURE;
        }

        $created = 0;
        $skipped = 0;
        $failed = 0;

        $this->info("Generating membership renewal invoices for {$year}...");

        User::query()
            ->whereIn('member_status', ['member', 'life_member'])
            ->select(['id', 'member_status'])
            ->chunkById(200, function ($users) use (
                $feeService,
                $year,
                &$created,
                &$skipped,
                &$failed
            ) {
                foreach ($users as $user) {
                    try {
                        $invoice = $feeService->createYearlyInvoice($user, $year);

                        if ($invoice && $invoice->wasRecentlyCreated) {
                            $created++;
                        } else {
                            $skipped++;
                        }
                    } catch (Throwable $exception) {
                        $failed++;

                        Log::error('Failed to generate yearly membership invoice.', [
                            'user_id' => $user->id,
                            'year' => $year,
                            'exception' => $exception->getMessage(),
                        ]);

                        $this->warn("Failed for user ID {$user->id}.");
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
