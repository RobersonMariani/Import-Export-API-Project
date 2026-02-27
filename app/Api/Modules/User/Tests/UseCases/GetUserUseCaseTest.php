<?php

namespace App\Api\Modules\User\Tests\UseCases;

use App\Api\Modules\User\Repositories\UserRepository;
use App\Api\Modules\User\UseCases\GetUserUseCase;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class GetUserUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnUserWhenUserExists(): void
    {
        // Arrange
        $expectedUser = new User(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($expectedUser) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with(1)
                    ->andReturn($expectedUser);
            }),
        );

        // Act
        $useCase = app()->make(GetUserUseCase::class);
        $result = $useCase->execute(1);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($expectedUser, $result);
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenUserDoesNotExist(): void
    {
        // Arrange
        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with(999)
                    ->andReturn(null);
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(GetUserUseCase::class);
        $useCase->execute(999);
    }
}
