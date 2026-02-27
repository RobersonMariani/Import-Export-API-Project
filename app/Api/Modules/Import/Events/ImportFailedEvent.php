<?php

namespace App\Api\Modules\Import\Events;

use App\Models\Import;

class ImportFailedEvent
{
    public function __construct(
        public Import $import,
    ) {}
}
