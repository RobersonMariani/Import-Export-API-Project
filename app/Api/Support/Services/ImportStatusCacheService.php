<?php

namespace App\Api\Support\Services;

use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Models\Import;
use Illuminate\Support\Facades\Cache;

class ImportStatusCacheService
{
    private const CACHE_PREFIX = 'import:status:';

    private const CACHE_TTL = 30;

    public function __construct(
        private readonly ImportRepository $importRepository,
    ) {}

    /** @return array<string, mixed> */
    public function getStatus(string $importId): array
    {
        return Cache::store('redis')->remember(
            self::CACHE_PREFIX.$importId,
            self::CACHE_TTL,
            function () use ($importId): array {
                $import = $this->importRepository->findById($importId);

                if ($import === null) {
                    return [];
                }

                return $this->toStatusArray($import);
            }
        );
    }

    public function invalidate(string $importId): void
    {
        Cache::store('redis')->forget(self::CACHE_PREFIX.$importId);
    }

    public function warmUp(Import $import): void
    {
        Cache::store('redis')->put(
            self::CACHE_PREFIX.$import->id,
            $this->toStatusArray($import),
            self::CACHE_TTL
        );
    }

    /** @return array<string, mixed> */
    private function toStatusArray(Import $import): array
    {
        return [
            'id' => $import->id,
            'status' => $import->status,
            'progress' => $import->progress,
            'total_records' => $import->total_records,
            'success_count' => $import->success_count,
            'failure_count' => $import->failure_count,
            'started_at' => $import->started_at?->toIso8601String(),
            'finished_at' => $import->finished_at?->toIso8601String(),
        ];
    }
}
