<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Integrations;

use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class GetImportsIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/imports';

    public function testShouldReturnPaginatedImportsWhenAuthenticated(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        Import::factory()->count(3)->create(['user_id' => $user->id]);

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
                    'progress',
                    'total_records',
                    'success_count',
                    'failure_count',
                    'original_filename',
                ],
            ],
            'links',
            'meta',
        ]);
    }

    public function testShouldFilterByStatusWhenQueryParamProvided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $token = auth('api')->login($user);
        Import::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        Import::factory()->create(['user_id' => $user->id, 'status' => 'queued']);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'?status=completed')
            ->assertOk();

        $response->assertJsonCount(1, 'data');
        $response->assertJsonPath('data.0.status', 'completed');
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
