<?php

namespace App\Api\Modules\Import\Events;

use App\Models\Import;

class ImportCompletedEvent
{
    public function __construct(
        public Import $import,
    ) {}
}
