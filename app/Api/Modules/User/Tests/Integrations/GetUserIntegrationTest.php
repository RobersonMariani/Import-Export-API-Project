<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\Integrations;

use App\Api\Modules\User\Tests\Assertables\UserAssertableJson;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class GetUserIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT_PREFIX = '/api/v1/users';

    public function testShouldReturnOkWithUserWhenUserExists(): void
    {
        // Arrange
        $authUser = User::factory()->create();
        $token = auth('api')->login($authUser);
        $targetUser = User::factory()->create([
            'name' => 'Target User',
            'email' => 'target@example.com',
        ]);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT_PREFIX.'/'.$targetUser->id)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    UserAssertableJson::schema($json);
                })->etc();
            })
            ->assertJsonPath('data.id', $targetUser->id)
            ->assertJsonPath('data.name', 'Target User')
            ->assertJsonPath('data.email', 'target@example.com');
    }

    public function testShouldReturnNotFoundWhenUserDoesNotExist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT_PREFIX.'/99999')
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::ENDPOINT_PREFIX.'/'.$user->id)
            ->assertUnauthorized();
    }
}
