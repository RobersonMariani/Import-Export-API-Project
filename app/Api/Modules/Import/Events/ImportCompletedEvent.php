<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Events;

use App\Models\Import;

class ImportCompletedEvent
{
    public function __construct(
        public Import $import,
    ) {}
}
