<?php

namespace App\Console\Commands;

use App\Models\CMW\History\HistoryItemPrice;
use Illuminate\Console\Command;

class CleanupItemPriceHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cleanup:item-price-history {--months=12 : Number of months to retain history}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete item price history records older than the specified retention period (default: 12 months)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $months = (int) $this->option('months');

        if ($months < 1) {
            $this->error('Retention period must be at least 1 month.');

            return self::FAILURE;
        }

        $cutoffDate = now()->subMonths($months);

        $this->info("Deleting item price history records older than {$cutoffDate->format('Y-m-d')}...");

        $deletedCount = HistoryItemPrice::where('changed_at', '<', $cutoffDate)->delete();

        $this->info("Successfully deleted {$deletedCount} records.");

        return self::SUCCESS;
    }
}
