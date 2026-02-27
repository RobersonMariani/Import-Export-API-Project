<?php

declare(strict_types=1);

namespace App\Api\Support\Services;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Models\Export;
use Illuminate\Support\Facades\Cache;

class ExportStatusCacheService
{
    private const CACHE_PREFIX = 'export:status:';

    private const CACHE_TTL = 30;

    public function __construct(
        private readonly ExportRepository $exportRepository,
    ) {}

    /** @return array<string, mixed> */
    public function getStatus(string $exportId): array
    {
        return Cache::store('redis')->remember(
            self::CACHE_PREFIX.$exportId,
            self::CACHE_TTL,
            function () use ($exportId): array {
                $export = $this->exportRepository->findById($exportId);

                if ($export === null) {
                    return [];
                }

                return $this->toStatusArray($export);
            },
        );
    }

    public function invalidate(string $exportId): void
    {
        Cache::store('redis')->forget(self::CACHE_PREFIX.$exportId);
    }

    public function warmUp(Export $export): void
    {
        Cache::store('redis')->put(
            self::CACHE_PREFIX.$export->id,
            $this->toStatusArray($export),
            self::CACHE_TTL,
        );
    }

    /** @return array<string, mixed> */
    private function toStatusArray(Export $export): array
    {
        return [
            'id' => $export->id,
            'status' => $export->status,
            'total_records' => $export->total_records,
            'compressed' => $export->compressed,
            'started_at' => $this->formatDate($export->started_at),
            'finished_at' => $this->formatDate($export->finished_at),
            'expires_at' => $this->formatDate($export->expires_at),
        ];
    }

    private function formatDate(mixed $value): ?string
    {
        return $value instanceof \Carbon\CarbonInterface ? $value->toIso8601String() : null;
    }
}
