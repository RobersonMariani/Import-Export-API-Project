<?php

namespace App\Api\Modules\Export\Tests\UseCases;

use App\Api\Modules\Export\Enums\ExportStatusEnum;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\Services\ExportService;
use App\Api\Modules\Export\UseCases\DownloadExportUseCase;
use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class DownloadExportUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnExportWhenCompletedAndFileExists(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->completed()->create(['user_id' => $user->id]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($export, $user) {
                $mock->shouldReceive('findByIdForUser')
                    ->once()
                    ->with($export->id, $user->id)
                    ->andReturn($export);
            }),
        );

        // Act
        $useCase = app()->make(DownloadExportUseCase::class);
        $result = $useCase->execute($export->id, $user->id);

        // Assert
        $this->assertInstanceOf(Export::class, $result);
        $this->assertEquals($export->id, $result->id);
        $this->assertEquals(ExportStatusEnum::Completed->value, $result->status);
        $this->assertNotNull($result->file_path);
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenExportNotFound(): void
    {
        // Arrange
        $user = User::factory()->create();
        $nonExistentId = '00000000-0000-0000-0000-000000000000';

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($nonExistentId, $user) {
                $mock->shouldReceive('findByIdForUser')
                    ->once()
                    ->with($nonExistentId, $user->id)
                    ->andReturn(null);
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);
        $this->expectExceptionMessage('Export não encontrado.');

        $useCase = app()->make(DownloadExportUseCase::class);
        $useCase->execute($nonExistentId, $user->id);
    }

    public function testExecuteShouldThrowRuntimeExceptionWhenExportNotCompleted(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->processing()->create(['user_id' => $user->id]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($export, $user) {
                $mock->shouldReceive('findByIdForUser')
                    ->once()
                    ->with($export->id, $user->id)
                    ->andReturn($export);
            }),
        );

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Export ainda não está disponível para download.');

        $useCase = app()->make(DownloadExportUseCase::class);
        $useCase->execute($export->id, $user->id);
    }

    public function testExecuteShouldThrowRuntimeExceptionWhenFilePathIsNull(): void
    {
        // Arrange
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'status' => ExportStatusEnum::Completed->value,
            'file_path' => null,
        ]);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($export, $user) {
                $mock->shouldReceive('findByIdForUser')
                    ->once()
                    ->with($export->id, $user->id)
                    ->andReturn($export);
            }),
        );

        // Act & Assert
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Arquivo de export não encontrado.');

        $useCase = app()->make(DownloadExportUseCase::class);
        $useCase->execute($export->id, $user->id);
    }

    public function testGetDownloadUrlShouldDelegateToExportService(): void
    {
        // Arrange
        $export = Export::factory()->completed()->create();
        $expectedUrl = 'https://example.com/temp/download-url';

        $this->instance(
            ExportService::class,
            Mockery::mock(ExportService::class, function (MockInterface $mock) use ($export, $expectedUrl) {
                $mock->shouldReceive('getTemporaryDownloadUrl')
                    ->once()
                    ->with($export)
                    ->andReturn($expectedUrl);
            }),
        );

        // Act
        $useCase = app()->make(DownloadExportUseCase::class);
        $result = $useCase->getDownloadUrl($export);

        // Assert
        $this->assertEquals($expectedUrl, $result);
    }
}
