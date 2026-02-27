<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\UseCases;

use App\Api\Modules\Auth\Services\AuthService;
use App\Api\Modules\Auth\UseCases\GetMeAuthUseCase;
use App\Models\User;
use Mockery;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class GetMeAuthUseCaseTest extends TestCase
{
    public function testExecuteShouldReturnUserWhenAuthenticated(): void
    {
        // Arrange
        $user = new User(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com']);

        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) use ($user) {
                $mock->shouldReceive('user')
                    ->once()
                    ->andReturn($user);
            }),
        );

        // Act
        $useCase = app()->make(GetMeAuthUseCase::class);
        $result = $useCase->execute();

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user, $result);
    }

    public function testExecuteShouldReturnNullWhenNotAuthenticated(): void
    {
        // Arrange
        $this->instance(
            AuthService::class,
            Mockery::mock(AuthService::class, function (MockInterface $mock) {
                $mock->shouldReceive('user')
                    ->once()
                    ->andReturn(null);
            }),
        );

        // Act
        $useCase = app()->make(GetMeAuthUseCase::class);
        $result = $useCase->execute();

        // Assert
        $this->assertNull($result);
    }
}
