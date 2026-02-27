<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Tests\Services;

use App\Api\Modules\Auth\Services\AuthService;
use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Mockery;
use PHPOpenSourceSaver\JWTAuth\JWTGuard;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\Group;
use Tests\TestCase;

#[Group('auth')]
class AuthServiceTest extends TestCase
{
    public function testAttemptShouldReturnTokenWhenCredentialsAreValid(): void
    {
        // Arrange
        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) {
            $mock->shouldReceive('attempt')
                ->once()
                ->with(['email' => 'test@example.com', 'password' => 'password'])
                ->andReturn('jwt-token-123');
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $result = $service->attempt(['email' => 'test@example.com', 'password' => 'password']);

        // Assert
        $this->assertEquals('jwt-token-123', $result);
    }

    public function testAttemptShouldReturnNullWhenCredentialsAreInvalid(): void
    {
        // Arrange
        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) {
            $mock->shouldReceive('attempt')
                ->once()
                ->andReturn(false);
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $result = $service->attempt(['email' => 'wrong@example.com', 'password' => 'wrong']);

        // Assert
        $this->assertNull($result);
    }

    public function testLoginUserShouldReturnTokenWhenUserIsProvided(): void
    {
        // Arrange
        $user = new User(['id' => 1, 'email' => 'test@example.com']);

        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('login')
                ->once()
                ->with($user)
                ->andReturn('jwt-token-456');
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $result = $service->loginUser($user);

        // Assert
        $this->assertEquals('jwt-token-456', $result);
    }

    public function testGetTokenTtlShouldReturnSecondsFromConfig(): void
    {
        // Arrange
        config(['jwt.ttl' => 60]);
        $authFactory = app(AuthFactory::class);
        $service = new AuthService($authFactory);

        // Act
        $result = $service->getTokenTtl();

        // Assert
        $this->assertEquals(3600, $result);
    }

    public function testUserShouldReturnUserWhenAuthenticated(): void
    {
        // Arrange
        $user = new User(['id' => 1, 'name' => 'Test', 'email' => 'test@example.com']);

        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) use ($user) {
            $mock->shouldReceive('user')
                ->once()
                ->andReturn($user);
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $result = $service->user();

        // Assert
        $this->assertInstanceOf(User::class, $result);
        $this->assertEquals($user, $result);
    }

    public function testUserShouldReturnNullWhenNotAuthenticated(): void
    {
        // Arrange
        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) {
            $mock->shouldReceive('user')
                ->once()
                ->andReturn(null);
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $result = $service->user();

        // Assert
        $this->assertNull($result);
    }

    public function testRefreshShouldReturnNewTokenWhenCalled(): void
    {
        // Arrange
        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) {
            $mock->shouldReceive('refresh')
                ->once()
                ->andReturn('refreshed-token-789');
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $result = $service->refresh();

        // Assert
        $this->assertEquals('refreshed-token-789', $result);
    }

    public function testLogoutShouldCallGuardLogoutWhenCalled(): void
    {
        // Arrange
        $guard = Mockery::mock(JWTGuard::class, function (MockInterface $mock) {
            $mock->shouldReceive('logout')
                ->once();
        });

        $authFactory = Mockery::mock(AuthFactory::class, function (MockInterface $mock) use ($guard) {
            $mock->shouldReceive('guard')
                ->with('api')
                ->andReturn($guard);
        });

        $service = new AuthService($authFactory);

        // Act
        $service->logout();

        // Assert — mock validates logout() was called once
    }
}
