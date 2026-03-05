<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\Services;

use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\Services\ExportService;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Models\Export;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\LazyCollection;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class ExportServiceTest extends TestCase
{
    use RefreshDatabase;

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
