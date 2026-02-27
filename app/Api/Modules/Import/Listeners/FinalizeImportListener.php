<?php

namespace App\Api\Modules\Import\Listeners;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Events\ImportBatchCompletedEvent;
use App\Api\Modules\Import\Repositories\ImportRepository;

class FinalizeImportListener
{
    public function __construct(
        private readonly ImportRepository $importRepository,
    ) {}

    public function handle(ImportBatchCompletedEvent $event): void
    {
        $import = $event->import->refresh();
        $batch = $event->batch;

        $status = $batch->totalJobs === $batch->failedJobs
            ? ImportStatusEnum::Failed
            : ($batch->hasFailures() ? ImportStatusEnum::Partial : ImportStatusEnum::Completed);

        $this->importRepository->updateStatus($import, $status->value, now());
    }
}
