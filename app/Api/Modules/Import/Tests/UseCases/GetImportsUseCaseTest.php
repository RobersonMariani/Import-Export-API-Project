<?php

namespace App\Api\Modules\Import\Tests\UseCases;

use App\Api\Modules\Import\Data\ImportQueryData;
use App\Api\Modules\Import\Repositories\ImportRepository;
use App\Api\Modules\Import\UseCases\GetImportsUseCase;
use App\Models\Import;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Pagination\LengthAwarePaginator as Paginator;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('import')]
class GetImportsUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnPaginatedImportsWhenCalled(): void
    {
        // Arrange
        $user = User::factory()->create();
        $imports = Import::factory()->count(3)->create(['user_id' => $user->id]);
        $query = ImportQueryData::validateAndCreate([]);
        $expectedPaginator = new Paginator($imports, 3, 15);

        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) use ($query, $user, $expectedPaginator) {
                $mock->shouldReceive('getAllPaginated')
                    ->once()
                    ->with($query, $user->id)
                    ->andReturn($expectedPaginator);
            }),
        );

        // Act
        $useCase = app()->make(GetImportsUseCase::class);
        $result = $useCase->execute($query, $user->id);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertCount(3, $result->items());
    }

    public function testExecuteShouldReturnPaginatedImportsWhenUserIdIsNull(): void
    {
        // Arrange
        $query = ImportQueryData::validateAndCreate(['status' => 'queued']);
        $expectedPaginator = new Paginator([], 0, 15);

        $this->instance(
            ImportRepository::class,
            Mockery::mock(ImportRepository::class, function (MockInterface $mock) use ($query, $expectedPaginator) {
                $mock->shouldReceive('getAllPaginated')
                    ->once()
                    ->with($query, null)
                    ->andReturn($expectedPaginator);
            }),
        );

        // Act
        $useCase = app()->make(GetImportsUseCase::class);
        $result = $useCase->execute($query, null);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
    }
}
