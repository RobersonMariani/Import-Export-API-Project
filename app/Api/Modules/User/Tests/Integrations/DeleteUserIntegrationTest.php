<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\Integrations;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class DeleteUserIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT_PREFIX = '/api/v1/users';

    public function testShouldReturnNoContentWhenUserExists(): void
    {
        // Arrange
        $authUser = User::factory()->create();
        $token = auth('api')->login($authUser);
        $targetUser = User::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson(self::ENDPOINT_PREFIX.'/'.$targetUser->id)
            ->assertNoContent();

        $this->assertSoftDeleted('users', ['id' => $targetUser->id]);
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
            ->deleteJson(self::ENDPOINT_PREFIX.'/99999')
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->deleteJson(self::ENDPOINT_PREFIX.'/'.$user->id)
            ->assertUnauthorized();
    }
}
