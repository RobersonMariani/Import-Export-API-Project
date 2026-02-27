<?php

namespace App\Api\Modules\Import\Events;

use App\Models\Import;

class ImportStartedEvent
{
    public function __construct(
        public Import $import,
    ) {}
}
