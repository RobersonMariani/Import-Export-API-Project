<?php

namespace App\Api\Modules\User\Tests\UseCases;

use App\Api\Modules\User\Repositories\UserRepository;
use App\Api\Modules\User\UseCases\DeleteUserUseCase;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class DeleteUserUseCaseTest extends TestCase
{
    public function testExecuteShouldDeleteUserWhenUserExists(): void
    {
        // Arrange
        $user = new User(['id' => 1, 'name' => 'Test User', 'email' => 'test@example.com']);

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with(1)
                    ->andReturn($user);
                $mock->shouldReceive('delete')
                    ->once()
                    ->with($user)
                    ->andReturn(true);
            }),
        );

        // Act
        $useCase = app()->make(DeleteUserUseCase::class);
        $useCase->execute(1);

        // Assert - mock verification ensures findById and delete were called
        $this->addToAssertionCount(1);
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
                $mock->shouldNotReceive('delete');
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(DeleteUserUseCase::class);
        $useCase->execute(999);
    }
}
