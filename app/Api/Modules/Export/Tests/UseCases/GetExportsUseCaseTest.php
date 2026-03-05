<?php

declare(strict_types=1);

namespace App\Api\Modules\Export\Tests\UseCases;

use App\Api\Modules\Export\Data\ExportQueryData;
use App\Api\Modules\Export\Repositories\ExportRepository;
use App\Api\Modules\Export\UseCases\GetExportsUseCase;
use App\Models\Export;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('export')]
class GetExportsUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnPaginatedExportsWhenCalled(): void
    {
        // Arrange
        $user = User::factory()->create();
        $exports = Export::factory()->count(3)->create(['user_id' => $user->id]);
        $query = ExportQueryData::validateAndCreate([]);
        $expectedPaginator = new Paginator($exports, 3, 15);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($query, $user, $expectedPaginator) {
                $mock->shouldReceive('getAllPaginated')
                    ->once()
                    ->with($query, $user->id)
                    ->andReturn($expectedPaginator);
            }),
        );

        // Act
        $useCase = app()->make(GetExportsUseCase::class);
        $result = $useCase->execute($query, $user->id);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
    }

    public function testExecuteShouldReturnPaginatedExportsWhenUserIdIsNull(): void
    {
        // Arrange
        $query = ExportQueryData::validateAndCreate(['status' => 'queued']);
        $expectedPaginator = new Paginator([], 0, 15);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($query, $expectedPaginator) {
                $mock->shouldReceive('getAllPaginated')
                    ->once()
                    ->with($query, null)
                    ->andReturn($expectedPaginator);
            }),
        );

        // Act
        $useCase = app()->make(GetExportsUseCase::class);
        $result = $useCase->execute($query, null);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(0, $result->items());
    }

    public function testExecuteShouldFilterByStatusWhenStatusProvided(): void
    {
        // Arrange
        $user = User::factory()->create();
        $completedExport = Export::factory()->create(['user_id' => $user->id, 'status' => 'completed']);
        $query = ExportQueryData::validateAndCreate(['status' => 'completed']);
        $expectedPaginator = new Paginator(collect([$completedExport]), 1, 15);

        $this->instance(
            ExportRepository::class,
            Mockery::mock(ExportRepository::class, function (MockInterface $mock) use ($query, $user, $expectedPaginator) {
                $mock->shouldReceive('getAllPaginated')
                    ->once()
                    ->with($query, $user->id)
                    ->andReturn($expectedPaginator);
            }),
        );

        // Act
        $useCase = app()->make(GetExportsUseCase::class);
        $result = $useCase->execute($query, $user->id);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(1, $result->items());
    }
}
