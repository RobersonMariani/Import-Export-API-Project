<?php

declare(strict_types=1);

namespace App\Api\Modules\Health\Tests\Assertables;

use Illuminate\Testing\Fluent\AssertableJson;

class HealthAssertableJson
{
    public static function schema(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('status', 'string')
            ->whereType('services', 'array')
            ->whereType('timestamp', 'string')
            ->etc();
    }
}
