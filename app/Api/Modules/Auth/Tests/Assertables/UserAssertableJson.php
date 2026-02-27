<?php

namespace App\Api\Modules\Auth\Tests\Assertables;

use Illuminate\Testing\Fluent\AssertableJson;

class UserAssertableJson
{
    public static function schema(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('id', 'integer')
            ->whereType('name', 'string')
            ->whereType('email', 'string')
            ->whereType('role', ['string', 'null'])
            ->whereType('created_at', 'string')
            ->whereType('updated_at', 'string')
            ->etc();
    }
}
