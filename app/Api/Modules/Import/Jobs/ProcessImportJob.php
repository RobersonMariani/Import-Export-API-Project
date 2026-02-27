<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Jobs;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Api\Modules\Import\Events\ImportStartedEvent;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\Services\ImportService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;

class ProcessImportJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $importId,
    ) {}

    public function handle(ImportRepository $importRepository, ImportService $importService): void
    {
        $import = $importRepository->findById($this->importId);

        if ($import === null) {
            return;
        }

        $fullPath = Storage::path($import->file_path);

        if (! file_exists($fullPath)) {
            $importRepository->updateStatus($import, ImportStatusEnum::Failed->value, now());
            Cache::forget('import:processing:'.$this->importId);

            return;
        }

        $importRepository->updateStatus($import, ImportStatusEnum::Processing->value);
        $importRepository->update($import, [
            'total_records' => $importService->getTotalRecords($fullPath),
            'started_at' => now(),
        ]);

        Event::dispatch(new ImportStartedEvent($import->refresh()));

        $chunks = $importService->getChunks($fullPath, 1000);
        $importService->dispatchChunkBatch($import->refresh(), $chunks);
    }
}
