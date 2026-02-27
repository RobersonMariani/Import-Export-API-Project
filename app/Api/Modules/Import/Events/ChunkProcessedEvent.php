<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Events;

use App\Models\Import;

class ChunkProcessedEvent
{
    public function __construct(
        public Import $import,
        public int $successCount,
        public int $failureCount,
    ) {}
}
