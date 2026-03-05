<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Integrations;

use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class DeleteImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/imports';

    public function testShouldReturnNoContentWhenImportDeleted(): void
    {
        // Arrange
        $user = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $user->id]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson(self::ENDPOINT.'/'.$import->id)
            ->assertNoContent();

        $this->assertDatabaseMissing('imports', ['id' => $import->id]);
    }

    public function testShouldReturnNotFoundWhenImportDoesNotExist(): void
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

    public function testShouldReturnNotFoundWhenImportBelongsToAnotherUser(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $import = Import::factory()->create(['user_id' => $owner->id]);
        $token = auth('api')->login($otherUser);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->deleteJson(self::ENDPOINT.'/'.$import->id)
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $import = Import::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->deleteJson(self::ENDPOINT.'/'.$import->id)
            ->assertUnauthorized();
    }
}
