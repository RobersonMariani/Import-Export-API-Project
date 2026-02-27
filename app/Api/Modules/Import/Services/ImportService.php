<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Services;

use App\Api\Modules\Import\Events\ImportBatchCompletedEvent;
use App\Api\Modules\Import\Jobs\ProcessImportChunkJob;
use App\Api\Modules\Import\Jobs\ProcessImportJob;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Models\Import;
use Generator;
use Illuminate\Bus\Batch;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use RuntimeException;
use Throwable;

class ImportService
{
    public function __construct(
        private readonly CsvParserService $csvParserService,
        private readonly ImportRepository $importRepository,
    ) {}

    private const CONCURRENCY_LOCK_PREFIX = 'import:processing:';

    private const LOCK_TTL_SECONDS = 3600;

    public function startImport(Import $import): void
    {
        $this->ensureConcurrencyControl($import);

        ProcessImportJob::dispatch($import->id)->onQueue('imports');
    }

    /**
     * @return Generator<int, array<int, array<string, string>>>
     */
    public function getChunks(string $filePath, int $chunkSize = 1000): Generator
    {
        return $this->csvParserService->readChunks($filePath, $chunkSize);
    }

    public function getTotalRecords(string $filePath): int
    {
        return $this->csvParserService->countRecords($filePath);
    }

    /**
     * @param iterable<int, array<int, array<string, string>>> $chunks
     */
    public function dispatchChunkBatch(Import $import, iterable $chunks): Batch
    {
        $jobs = [];

        foreach ($chunks as $index => $chunk) {
            $jobs[] = new ProcessImportChunkJob($import->id, $chunk, $index);
        }

        return Bus::batch($jobs)
            ->name('Import '.$import->id)
            ->onQueue('imports')
            ->allowFailures()
            ->then(function (Batch $batch) use ($import): void {
                Event::dispatch(new ImportBatchCompletedEvent($import->refresh(), $batch));
            })
            ->catch(function (Batch $batch, Throwable $e) use ($import): void {
                $this->handleBatchFailure($import, $batch, $e);
            })
            ->finally(function (Batch $batch) use ($import): void {
                $this->releaseConcurrencyLock($import);
            })
            ->dispatch();
    }

    public function handleBatchFailure(Import $import, Batch $batch, Throwable $e): void
    {
        $refreshed = $this->importRepository->findById($import->id);

        if ($refreshed !== null) {
            $metadata = array_merge($refreshed->metadata ?? [], ['batch_error' => $e->getMessage()]);
            $this->importRepository->update($refreshed, ['metadata' => $metadata]);
        }
    }

    private function ensureConcurrencyControl(Import $import): void
    {
        $lockKey = self::CONCURRENCY_LOCK_PREFIX.$import->id;

        if (Cache::has($lockKey)) {
            throw new RuntimeException('Import já está em processamento');
        }

        Cache::put($lockKey, true, self::LOCK_TTL_SECONDS);
    }

    private function releaseConcurrencyLock(Import $import): void
    {
        Cache::forget(self::CONCURRENCY_LOCK_PREFIX.$import->id);
    }
}
