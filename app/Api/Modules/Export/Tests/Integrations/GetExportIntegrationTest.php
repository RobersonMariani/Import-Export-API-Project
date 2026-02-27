<?php

namespace App\Api\Modules\Export\Tests\Integrations;

use App\Api\Modules\Export\Tests\Assertables\ExportAssertableJson;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class GetExportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/exports';

    public function testShouldReturnExportWhenFoundForUser(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->create(['user_id' => $user->id]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$export->id)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', function (AssertableJson $json) {
                    ExportAssertableJson::schema($json);
                })->etc();
            });
    }

    public function testShouldReturnNotFoundWhenExportDoesNotExist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$nonExistentId)
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
            ->getJson(self::ENDPOINT.'/'.$export->id)
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $export = Export::factory()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::ENDPOINT.'/'.$export->id)
            ->assertUnauthorized();
    }
}
