<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Services;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\Services\ExportService;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Models\Export;
use App\Models\User;
use DateTimeInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use RuntimeException;
use Tests\TestCase;

#[Group('export')]
class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

    public function testGetTemporaryDownloadUrlShouldReturnUrlWhenFileExists(): void
    {
        // Arrange
        $export = Export::factory()->completed()->create();

        $diskMock = Mockery::mock();
        $diskMock->shouldReceive('temporaryUrl')
            ->once()
            ->with($export->file_path, Mockery::type(DateTimeInterface::class))
            ->andReturn('https://example.com/temp/download');

        Storage::shouldReceive('disk')
            ->with('local')
            ->andReturn($diskMock);

        $service = new ExportService(
            Mockery::mock(UserRepository::class),
            Mockery::mock(ExportRepository::class),
        );

        // Act
        $result = $service->getTemporaryDownloadUrl($export);

        // Assert
        $this->assertEquals('https://example.com/temp/download', $result);
    }

    public function testGetTemporaryDownloadUrlShouldThrowWhenFilePathIsNull(): void
    {
        // Arrange
        $export = Export::factory()->create([
            'file_path' => null,
            'status' => 'completed',
        ]);

        $service = new ExportService(
            Mockery::mock(UserRepository::class),
            Mockery::mock(ExportRepository::class),
        );

        // Act & Assert
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Export ainda não possui arquivo disponível');

        $service->getTemporaryDownloadUrl($export);
    }

    public function testProcessExportShouldCreateCsvAndUpdateRepository(): void
    {
        // Arrange
        Storage::fake('local');
        $user = User::factory()->create();
        $export = Export::factory()->create([
            'user_id' => $user->id,
            'filters' => [],
            'compressed' => false,
        ]);

        $cursor = LazyCollection::make([$user]);

        $userRepoMock = Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($cursor) {
            $mock->shouldReceive('getCursorForExport')
                ->once()
                ->with([])
                ->andReturn($cursor);
        });

        $exportRepoMock = Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($export) {
            $mock->shouldReceive('update')
                ->once()
                ->with(Mockery::type(Export::class), Mockery::on(function (array $data) {
                    return str_starts_with($data['file_path'] ?? '', 'exports/')
                        && $data['total_records'] === 1
                        && isset($data['expires_at']);
                }))
                ->andReturn($export);
        });

        $service = new ExportService($userRepoMock, $exportRepoMock);

        // Act
        $service->processExport($export);

        // Assert - mock verifies update was called
        $this->addToAssertionCount(1);
    }
}
