<?php

declare(strict_types=1);

namespace App\Api\Modules\Import\Tests\Integrations;

use App\Api\Modules\Import\Enums\ImportStatusEnum;
use App\Models\Import;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class RetryImportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/imports';

    public function testShouldReturnAcceptedWhenImportRetried(): void
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create();
        $import = Import::factory()->create([
            'user_id' => $user->id,
            'status' => ImportStatusEnum::Failed->value,
        ]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT.'/'.$import->id.'/retry')
            ->assertStatus(202);
    }

    public function testShouldReturnServerErrorWhenImportNotFailed(): void
    {
        // Arrange
        $user = User::factory()->create();
        $import = Import::factory()->create([
            'user_id' => $user->id,
            'status' => ImportStatusEnum::Completed->value,
        ]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT.'/'.$import->id.'/retry')
            ->assertStatus(500);
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
            ->postJson(self::ENDPOINT.'/00000000-0000-0000-0000-000000000000/retry')
            ->assertNotFound();
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->postJson(self::ENDPOINT.'/some-id/retry')
            ->assertUnauthorized();
    }
}
