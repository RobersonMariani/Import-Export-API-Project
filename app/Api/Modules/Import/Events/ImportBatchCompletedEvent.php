<?php

namespace App\Api\Modules\Import\Events;

use App\Models\Import;
use Illuminate\Bus\Batch;

class ImportBatchCompletedEvent
{
    public function __construct(
        public Import $import,
        public Batch $batch,
    ) {}
}
