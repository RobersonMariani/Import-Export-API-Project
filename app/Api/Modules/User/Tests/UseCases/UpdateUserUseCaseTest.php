<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\UseCases;

use App\Api\Modules\User\Data\UpdateUserData;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Api\Modules\User\UseCases\UpdateUserUseCase;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class UpdateUserUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnUpdatedUserWhenUserExists(): void
    {
        // Arrange
        $user = new User(['id' => 1, 'name' => 'Old Name', 'email' => 'old@example.com']);
        $updatedUser = new User(['id' => 1, 'name' => 'New Name', 'email' => 'new@example.com']);
        $data = new UpdateUserData(name: 'New Name', email: 'new@example.com');

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($user, $updatedUser) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with(1)
                    ->andReturn($user);
                $mock->shouldReceive('update')
                    ->once()
                    ->with($user, Mockery::on(function (array $data) {
                        return $data['name'] === 'New Name' && $data['email'] === 'new@example.com';
                    }))
                    ->andReturn($updatedUser);
            }),
        );

        // Act
        $useCase = app()->make(UpdateUserUseCase::class);
        $result = $useCase->execute(1, $data);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals('New Name', $result->name);
        $this->assertEquals('new@example.com', $result->email);
    }

    public function testExecuteShouldThrowModelNotFoundExceptionWhenUserDoesNotExist(): void
    {
        // Arrange
        $data = new UpdateUserData(name: 'New Name');

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) {
                $mock->shouldReceive('findById')
                    ->once()
                    ->with(999)
                    ->andReturn(null);
                $mock->shouldNotReceive('update');
            }),
        );

        // Act & Assert
        $this->expectException(ModelNotFoundException::class);

        $useCase = app()->make(UpdateUserUseCase::class);
        $useCase->execute(999, $data);
    }
}
