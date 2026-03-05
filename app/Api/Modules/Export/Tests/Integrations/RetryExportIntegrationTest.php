<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Integrations;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class RetryExportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/exports';

    public function testShouldReturnAcceptedWhenExportRetried(): void
    {
        // Arrange
        Queue::fake();
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => ExportStatusEnum::Failed->value,
        ]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT.'/'.$export->id.'/retry')
            ->assertStatus(202);
    }

    public function testShouldReturnServerErrorWhenExportNotFailed(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => ExportStatusEnum::Completed->value,
        ]);
        $token = auth('api')->login($user);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->postJson(self::ENDPOINT.'/'.$export->id.'/retry')
            ->assertStatus(500);
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
