<?php

namespace App\Api\Modules\Auth\Tests\Integrations;

use App\Api\Modules\Auth\Tests\Assertables\UserAssertableJson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class GetMeAuthIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/auth/me';

    public function testShouldReturnOkWithUserWhenAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'me@example.com',
        ]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    UserAssertableJson::schema($json);
                });
            });
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::ENDPOINT)
            ->assertUnauthorized();
    }
}
