<?php

namespace App\Api\Modules\Export\Jobs;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\Services\ExportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class ProcessExportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $timeout = 600;

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
        } catch (\Throwable $e) {
            $exportRepository->updateStatus($export->refresh(), ExportStatusEnum::Failed->value, now());

            throw $e;
        }
    }
}
