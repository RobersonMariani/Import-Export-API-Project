<?php

declare(strict_types=1);

namespace App\Api\Modules\Health\UseCases;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Import\Repositories\ImportRepository;
use Illuminate\Support\Facades\Queue;

class GetMetricsUseCase
{
    private const QUEUES = ['default', 'imports', 'exports'];

    public function __construct(
        private readonly ImportRepository $importRepository,
        private readonly ExportRepository $exportRepository,
    ) {}

    /** @return array{import_counts: array<string, int>, export_counts: array<string, int>, queue_sizes: array<string, int>} */
    public function execute(): array
    {
        return [
            'import_counts' => $this->importRepository->countByStatus(),
            'export_counts' => $this->exportRepository->countByStatus(),
            'queue_sizes' => $this->getQueueSizes(),
        ];
    }

    /** @return array<string, int> */
    private function getQueueSizes(): array
    {
        $sizes = [];

        foreach (self::QUEUES as $queue) {
            $sizes[$queue] = Queue::connection()->size($queue);
        }

        return $sizes;
    }
}
