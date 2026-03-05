<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\UseCases;

use App\Api\Modules\Export\Data\CreateExportData;
use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Jobs\ProcessExportJob;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Models\Export;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CreateExportUseCase
{
    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    public function execute(CreateExportData $data, int $userId): Export
    {
        $export = DB::transaction(function () use ($data, $userId): Export {
            return $this->exportRepository->create([
                'user_id' => $userId,
                'status' => ExportStatusEnum::Queued->value,
                'file_path' => null,
                'filters' => $data->filters?->toArray(),
                'total_records' => 0,
                'compressed' => $data->compressed,
                'expires_at' => null,
                'started_at' => null,
                'finished_at' => null,
            ]);
        });

        $this->dispatchWithRetry($export);

        return $export;
    }

    private function dispatchWithRetry(Export $export, int $maxRetries = 3): void
    {
        $lastException = null;

        for ($attempt = 1; $attempt <= $maxRetries; $attempt++) {
            try {
                ProcessExportJob::dispatch($export->id)->onQueue('exports');

                return;
            } catch (\Throwable $e) {
                $lastException = $e;
                Log::warning('Export dispatch attempt failed', [
                    'export_id' => $export->id,
                    'attempt' => $attempt,
                    'error' => $e->getMessage(),
                ]);

                if ($attempt < $maxRetries) {
                    usleep($attempt * 100_000);
                }
            }
        }

        $this->exportRepository->update($export, [
            'status' => ExportStatusEnum::Failed->value,
            'error_message' => 'Falha ao despachar job após '.$maxRetries.' tentativas: '.$lastException?->getMessage(),
            'finished_at' => now(),
        ]);

        Log::error('Export dispatch failed permanently', [
            'export_id' => $export->id,
            'error' => $lastException?->getMessage(),
        ]);
    }
}
