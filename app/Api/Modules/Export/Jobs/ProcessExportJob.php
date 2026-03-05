<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Jobs;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

    /** @var list<int> */
    public array $backoff = [10, 30, 60];

    public function __construct(
        public string $exportId,
    ) {}

    public function handle(ExportRepository $exportRepository, ExportService $exportService): void
    {
        $export = $exportRepository->findById($this->exportId);

        if ($export === null) {
            return;
        }

        $exportRepository->updateStatus($export, ExportStatusEnum::Processing->value);
        $exportRepository->update($export, ['started_at' => now()]);

        try {
            $exportService->processExport($export->refresh());
            $exportRepository->updateStatus($export->refresh(), ExportStatusEnum::Completed->value, now());
        } catch (Throwable $e) {
            $exportRepository->update($export->refresh(), [
                'status' => ExportStatusEnum::Failed->value,
                'error_message' => mb_substr($e->getMessage(), 0, 2000),
                'finished_at' => now(),
            ]);

            throw $e;
        }
    }

    public function failed(Throwable $exception): void
    {
        Log::error('ProcessExportJob failed permanently', [
            'export_id' => $this->exportId,
            'error' => $exception->getMessage(),
        ]);

        $exportRepository = app(ExportRepository::class);
        $export = $exportRepository->findById($this->exportId);

        if ($export === null) {
            return;
        }

        $exportRepository->update($export, [
            'status' => ExportStatusEnum::Failed->value,
            'error_message' => mb_substr($exception->getMessage(), 0, 2000),
            'finished_at' => now(),
        ]);
    }
}
