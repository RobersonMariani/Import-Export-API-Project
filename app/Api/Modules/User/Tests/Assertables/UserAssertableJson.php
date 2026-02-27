<?php

namespace App\Api\Modules\User\Tests\Assertables;

use Illuminate\Testing\Fluent\AssertableJson;

class UserAssertableJson
{
    public static function schema(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('id', 'integer')
            ->whereType('name', 'string')
            ->whereType('email', 'string')
            ->whereType('phone', ['string', 'null'])
            ->whereType('address', ['string', 'null'])
            ->whereType('city', ['string', 'null'])
            ->whereType('state', ['string', 'null'])
            ->whereType('zip_code', ['string', 'null'])
            ->whereType('birth_date', ['string', 'null'])
            ->whereType('role', 'string')
            ->whereType('role_label', 'string')
            ->whereType('created_at', 'string')
            ->whereType('updated_at', 'string')
            ->etc();
    }
}
