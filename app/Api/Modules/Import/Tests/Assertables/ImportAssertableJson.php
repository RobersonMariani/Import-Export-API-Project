<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Assertables;

use Illuminate\Testing\Fluent\AssertableJson;

class ImportAssertableJson
{
    public static function schema(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('id', 'string')
            ->whereType('status', 'string')
            ->whereType('status_label', ['string', 'null'])
            ->whereType('progress', 'integer')
            ->whereType('total_records', 'integer')
            ->whereType('success_count', 'integer')
            ->whereType('failure_count', 'integer')
            ->whereType('original_filename', 'string')
            ->whereType('started_at', ['string', 'null'])
            ->whereType('finished_at', ['string', 'null'])
            ->whereType('processing_time_seconds', ['integer', 'null'])
            ->whereType('estimated_remaining_seconds', ['integer', 'null'])
            ->whereType('created_at', ['string', 'null'])
            ->etc();
    }
}
