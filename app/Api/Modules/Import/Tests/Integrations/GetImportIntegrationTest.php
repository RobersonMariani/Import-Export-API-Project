<?php

namespace App\Api\Modules\Import\Tests\Integrations;

use App\Api\Modules\Import\Tests\Assertables\ImportAssertableJson;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class GetImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/imports';

    public function testShouldReturnImportWhenFoundAndUserOwnsIt(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $import = Import::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$import->id)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) use ($import) {
                $json->has('data', function (AssertableJson $json) use ($import) {
                    ImportAssertableJson::schema($json);
                    $json->where('id', $import->id)->etc();
                })->etc();
            });
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
            ->getJson(self::ENDPOINT.'/'.'00000000-0000-0000-0000-000000000000')
            ->assertNotFound();
    }

    public function testShouldReturnNotFoundWhenUserDoesNotOwnImport(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = auth('api')->login($otherUser);
        $import = Import::factory()->create(['user_id' => $owner->id]);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$import->id)
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $import = Import::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::ENDPOINT.'/'.$import->id)
            ->assertUnauthorized();
    }
}
