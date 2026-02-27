<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Assertables;

use Illuminate\Testing\Fluent\AssertableJson;

class ExportAssertableJson
{
    public static function schema(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('id', 'string')
            ->whereType('status', 'string')
            ->whereType('status_label', ['string', 'null'])
            ->whereType('total_records', 'integer')
            ->whereType('compressed', 'boolean')
            ->whereType('file_path', ['string', 'null'])
            ->whereType('download_url', ['string', 'null'])
            ->whereType('expires_at', ['string', 'null'])
            ->whereType('processing_time_seconds', ['integer', 'null'])
            ->whereType('started_at', ['string', 'null'])
            ->whereType('finished_at', ['string', 'null'])
            ->whereType('created_at', ['string', 'null'])
            ->etc();
    }
}
