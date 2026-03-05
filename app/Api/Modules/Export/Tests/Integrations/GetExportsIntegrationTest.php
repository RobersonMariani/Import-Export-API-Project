<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Integrations;

use App\Api\Modules\Export\Tests\Assertables\ExportAssertableJson;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\Fluent\AssertableJson;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class GetExportsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/exports';

    public function testShouldReturnPaginatedExportsWhenAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        Export::factory()->count(3)->create(['user_id' => $user->id]);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT)
            ->assertOk();

        $response->assertJsonStructure([
            'data' => [
                '*' => [
                    'id',
                    'status',
                    'status_label',
                    'total_records',
                    'compressed',
                    'created_at',
                ],
            ],
            'links',
            'meta',
        ]);

        $response->assertJsonCount(3, 'data');
    }

    public function testShouldReturnExportsWithCorrectSchemaWhenAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        Export::factory()->create(['user_id' => $user->id]);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT)
            ->assertOk()
            ->assertJson(function (AssertableJson $json) {
                $json->has('data', 1, function (AssertableJson $json) {
                    ExportAssertableJson::schema($json);
                })->etc();
            });
    }

    public function testShouldFilterByStatusWhenQueryParamProvided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        Export::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Export::factory()->create(['user_id' => $user->id, 'status' => 'queued']);
        Export::factory()->create(['user_id' => $user->id, 'status' => 'processing']);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'?status=completed')
            ->assertOk();

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', 'completed');
    }

    public function testShouldReturnOnlyUserExportsWhenAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $token = auth('api')->login($user);
        Export::factory()->count(2)->create(['user_id' => $user->id]);
        Export::factory()->count(3)->create(['user_id' => $otherUser->id]);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT)
            ->assertOk();

        $response->assertJsonCount(2, 'data');
    }

    public function testShouldReturnEmptyDataWhenNoExportsExist(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT)
            ->assertOk();

        $response->assertJsonCount(0, 'data');
        $response->assertJsonPath('meta.total', 0);
    }

    public function testShouldRespectPaginationParametersWhenProvided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        Export::factory()->count(5)->create(['user_id' => $user->id]);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'?per_page=2&page=1')
            ->assertOk();

        $response->assertJsonCount(2, 'data');
        $response->assertJsonPath('meta.per_page', 2);
        $response->assertJsonPath('meta.current_page', 1);
        $response->assertJsonPath('meta.total', 5);
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::ENDPOINT)
            ->assertUnauthorized();
    }

    public function testShouldReturnValidationErrorWhenStatusIsInvalid(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'?status=invalid_status')
            ->assertUnprocessable();
    }
}
