<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Integrations;

use App\Api\Modules\Export\Services\ExportService;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class DownloadExportIntegrationTest extends TestCase
{
    use RefreshDatabase;

    private const ENDPOINT = '/api/v1/exports';

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function testShouldReturnDownloadUrlWhenExportCompleted(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->completed()->create(['user_id' => $user->id]);
        Storage::disk('local')->put($export->file_path, 'csv,content');

        $this->instance(
            ExportService::class,
            Mockery::mock(ExportService::class, function (MockInterface $mock) {
                $mock->shouldReceive('getTemporaryDownloadUrl')
                    ->andReturn('https://example.com/temp/download-url');
            }),
        );

        $token = auth('api')->login($user);

        // Act & Assert
        $response = $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$export->id.'/download')
            ->assertOk()
            ->assertJsonStructure(['download_url']);

        $this->assertEquals('https://example.com/temp/download-url', $response->json('download_url'));
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
            ->getJson(self::ENDPOINT.'/'.$nonExistentId.'/download')
            ->assertNotFound();
    }

    public function testShouldReturnNotFoundWhenExportBelongsToAnotherUser(): void
    {
        // Arrange
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $export = Export::factory()->completed()->create(['user_id' => $owner->id]);
        $token = auth('api')->login($otherUser);

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$export->id.'/download')
            ->assertNotFound();
    }

    public function testShouldReturnErrorWhenExportNotCompleted(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->processing()->create(['user_id' => $user->id]);
        $token = auth('api')->login($user);

        // Act & Assert - DownloadExportUseCase throws RuntimeException when not completed
        $this
            ->withHeader('Accept', 'application/json')
            ->withHeader('Authorization', 'Bearer '.$token)
            ->getJson(self::ENDPOINT.'/'.$export->id.'/download')
            ->assertStatus(500);
    }

    public function testShouldReturnUnauthorizedWhenNotAuthenticated(): void
    {
        // Arrange
        $export = Export::factory()->completed()->create();

        // Act & Assert
        $this
            ->withHeader('Accept', 'application/json')
            ->getJson(self::ENDPOINT.'/'.$export->id.'/download')
            ->assertUnauthorized();
    }
}
