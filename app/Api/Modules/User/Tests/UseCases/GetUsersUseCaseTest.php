<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\UseCases;

use App\Api\Modules\User\Data\UserQueryData;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Api\Modules\User\UseCases\GetUsersUseCase;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\LengthAwarePaginator as LengthAwarePaginatorClass;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class GetUsersUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnPaginatorWhenQueryIsValid(): void
    {
        // Arrange
        $query = UserQueryData::validateAndCreate([]);
        $expectedPaginator = new LengthAwarePaginatorClass([], 0, 15, 1);

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($expectedPaginator) {
                $mock->shouldReceive('getAllPaginated')
                    ->once()
                    ->with(Mockery::type(UserQueryData::class))
                    ->andReturn($expectedPaginator);
            }),
        );

        // Act
        $useCase = app()->make(GetUsersUseCase::class);
        $result = $useCase->execute($query);

        // Assert
        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertEquals($expectedPaginator, $result);
    }
}
