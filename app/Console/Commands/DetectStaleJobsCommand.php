<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Jobs\ProcessImportJob;
use App\Models\Export;
use App\Models\Import;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redis;

class DetectStaleJobsCommand extends Command
{
    protected $signature = 'jobs:detect-stale
        {--minutes=30 : Minutes threshold for processing jobs}
        {--queued-minutes=5 : Minutes threshold for queued jobs (re-dispatch or fail)}';

    protected $description = 'Detect stale imports/exports: re-dispatches orphaned queued jobs and fails timed-out processing jobs';

    public function handle(): int
    {
        $processingMinutes = (int) $this->option('minutes');
        $queuedMinutes = (int) $this->option('queued-minutes');

        $redispatched = $this->redispatchOrphanedImports(now()->subMinutes($queuedMinutes));
        $staleImports = $this->handleStaleImports(now()->subMinutes($processingMinutes));
        $staleExports = $this->handleStaleExports(now()->subMinutes($processingMinutes));

        if ($redispatched > 0) {
            $this->info("Re-dispatched {$redispatched} orphaned queued imports.");
            Log::info('Orphaned queued imports re-dispatched', ['count' => $redispatched]);
        }

        $total = $staleImports + $staleExports;

        if ($total > 0) {
            $this->info("Marked {$total} stale jobs as failed ({$staleImports} imports, {$staleExports} exports).");
            Log::warning('Stale jobs detected and marked as failed', [
                'imports' => $staleImports,
                'exports' => $staleExports,
                'processing_threshold_minutes' => $processingMinutes,
            ]);
        }

        if ($redispatched === 0 && $total === 0) {
            $this->info('No stale jobs detected.');
        }

        return self::SUCCESS;
    }

    private function redispatchOrphanedImports(\Illuminate\Support\Carbon $threshold): int
    {
        $queueSize = (int) Redis::llen('queues:imports');

        $orphaned = Import::query()
            ->where('status', ImportStatusEnum::Queued->value)
            ->where('updated_at', '<', $threshold)
            ->whereNull('started_at')
            ->get();

        if ($orphaned->isEmpty()) {
            return 0;
        }

        $count = 0;

        foreach ($orphaned as $import) {
            try {
                ProcessImportJob::dispatch($import->id)->onQueue('imports');
                $import->touch();
                $count++;
            } catch (\Throwable $e) {
                $import->update([
                    'status' => ImportStatusEnum::Failed->value,
                    'error_message' => 'Falha ao re-despachar job órfão: '.$e->getMessage(),
                    'finished_at' => now(),
                ]);
                Log::error('Failed to re-dispatch orphaned import', [
                    'import_id' => $import->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $count;
    }

    private function handleStaleImports(\Illuminate\Support\Carbon $threshold): int
    {
        return Import::query()
            ->where('status', ImportStatusEnum::Processing->value)
            ->where('updated_at', '<', $threshold)
            ->update([
                'status' => ImportStatusEnum::Failed->value,
                'error_message' => 'Importação marcada como falha automaticamente: tempo limite excedido.',
                'finished_at' => now(),
            ]);
    }

    private function handleStaleExports(\Illuminate\Support\Carbon $threshold): int
    {
        $staleStatuses = [ExportStatusEnum::Queued->value, ExportStatusEnum::Processing->value];

        return Export::query()
            ->whereIn('status', $staleStatuses)
            ->where('updated_at', '<', $threshold)
            ->update([
                'status' => ExportStatusEnum::Failed->value,
                'error_message' => 'Exportação marcada como falha automaticamente: tempo limite excedido.',
                'finished_at' => now(),
            ]);
    }
}
