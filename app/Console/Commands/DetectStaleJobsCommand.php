<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Models\Export;
use App\Models\Import;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DetectStaleJobsCommand extends Command
{
    protected $signature = 'jobs:detect-stale {--minutes=30 : Minutes threshold to consider a job stale}';

    protected $description = 'Detect and mark stale imports/exports that have been processing or queued for too long';

    public function handle(): int
    {
        $minutes = (int) $this->option('minutes');
        $threshold = now()->subMinutes($minutes);

        $staleImports = $this->handleStaleImports($threshold);
        $staleExports = $this->handleStaleExports($threshold);

        $total = $staleImports + $staleExports;

        if ($total > 0) {
            $this->info("Marked {$total} stale jobs as failed ({$staleImports} imports, {$staleExports} exports).");
            Log::warning('Stale jobs detected and marked as failed', [
                'imports' => $staleImports,
                'exports' => $staleExports,
                'threshold_minutes' => $minutes,
            ]);
        } else {
            $this->info('No stale jobs detected.');
        }

        return self::SUCCESS;
    }

    private function handleStaleImports(\Illuminate\Support\Carbon $threshold): int
    {
        $staleStatuses = [ImportStatusEnum::Queued->value, ImportStatusEnum::Processing->value];

        $count = Import::query()
            ->whereIn('status', $staleStatuses)
            ->where('updated_at', '<', $threshold)
            ->update([
                'status' => ImportStatusEnum::Failed->value,
                'error_message' => 'Importação marcada como falha automaticamente: tempo limite excedido.',
                'finished_at' => now(),
            ]);

        return $count;
    }

    private function handleStaleExports(\Illuminate\Support\Carbon $threshold): int
    {
        $staleStatuses = [ExportStatusEnum::Queued->value, ExportStatusEnum::Processing->value];

        $count = Export::query()
            ->whereIn('status', $staleStatuses)
            ->where('updated_at', '<', $threshold)
            ->update([
                'status' => ExportStatusEnum::Failed->value,
                'error_message' => 'Exportação marcada como falha automaticamente: tempo limite excedido.',
                'finished_at' => now(),
            ]);

        return $count;
    }
}
