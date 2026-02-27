<?php

declare(strict_types=1);

namespace App\Api\Modules\User\Tests\UseCases;

use App\Api\Modules\User\Data\CreateUserData;
use App\Api\Modules\User\Repositories\UserRepository;
use App\Api\Modules\User\UseCases\CreateUserUseCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('user')]
class CreateUserUseCaseTest extends TestCase
{
    use RefreshDatabase;

    public function testExecuteShouldReturnUserWhenDataIsValid(): void
    {
        // Arrange
        $data = CreateUserData::validateAndCreate([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);
        $expectedUser = User::factory()->make([
            'id' => 1,
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        $this->instance(
            UserRepository::class,
            Mockery::mock(UserRepository::class, function (MockInterface $mock) use ($expectedUser) {
                $mock->shouldReceive('create')
                    ->once()
                    ->with(Mockery::on(function (array $data) {
                        return $data['name'] === 'Test User'
                            && $data['email'] === 'test@example.com'
                            && isset($data['password']);
                    }))
                    ->andReturn($expectedUser);
            }),
        );

        // Act
        $useCase = app()->make(CreateUserUseCase::class);
        $result = $useCase->execute($data);

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($expectedUser->name, $result->name);
        $this->assertEquals($expectedUser->email, $result->email);
    }
}
