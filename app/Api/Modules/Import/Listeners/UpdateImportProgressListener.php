<?php

namespace App\Api\Modules\Import\Listeners;

use App\Api\Modules\Import\Events\ChunkProcessedEvent;
use App\Api\Modules\Import\Repositories\ImportRepository;

class UpdateImportProgressListener
{
    public function __construct(
        private readonly ImportRepository $importRepository,
    ) {}

    public function handle(ChunkProcessedEvent $event): void
    {
        $import = $event->import->refresh();

        $progressPercent = $import->total_records > 0
            ? (int) round(($import->progress / $import->total_records) * 100)
            : 0;

        $this->importRepository->update($import, [
            'metadata' => array_merge($import->metadata ?? [], [
                'progress_percent' => $progressPercent,
            ]),
        ]);
    }
}
