<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\Assertables;

use Illuminate\Testing\Fluent\AssertableJson;

class AuthTokenAssertableJson
{
    public static function schema(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('access_token', 'string')
            ->whereType('token_type', 'string')
            ->whereType('expires_in', 'integer')
            ->where('token_type', 'bearer')
            ->etc();
    }

    public static function schemaWithUser(AssertableJson $json): AssertableJson
    {
        return $json
            ->whereType('access_token', 'string')
            ->whereType('token_type', 'string')
            ->whereType('expires_in', 'integer')
            ->where('token_type', 'bearer')
            ->has('user', function (AssertableJson $json) {
                UserAssertableJson::schema($json);
            })
            ->etc();
    }
}
