<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Integrations;

use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class DeleteExportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/exports';

    public function testShouldReturnNoContentWhenExportDeleted(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $user->id]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson(self::ENDPOINT.'/'.$export->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('exports', ['id' => $export->id]);
    }

    public function testShouldReturnNotFoundWhenExportDoesNotExist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson(self::ENDPOINT.'/00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function testShouldReturnNotFoundWhenExportBelongsToAnotherUser(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $owner->id]);
        $token = auth('api')->login($otherUser);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson(self::ENDPOINT.'/'.$export->id)
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $export = Export::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->deleteJson(self::ENDPOINT.'/'.$export->id)
            ->assertUnauthorized();
    }
}
