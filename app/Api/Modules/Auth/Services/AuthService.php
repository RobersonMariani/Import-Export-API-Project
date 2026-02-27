<?php

namespace App\Api\Modules\Auth\Services;

use App\Models\User;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class AuthService
{
    public function __construct(
        private readonly AuthFactory $auth,
    ) {}

    private function guard(): \Illuminate\Contracts\Auth\Guard
    {
        return $this->auth->guard('api');
    }

    /** @param  array<string, string>  $credentials */
    public function attempt(array $credentials): ?string
    {
        $token = $this->guard()->attempt($credentials);

        return $token !== false ? $token : null;
    }

    public function loginUser(User $user): string
    {
        return $this->guard()->login($user);
    }

    public function refresh(): string
    {
        return $this->guard()->refresh();
    }

    public function logout(): void
    {
        $this->guard()->logout();
    }

    public function user(): ?User
    {
        $user = $this->guard()->user();

        return $user instanceof User ? $user : null;
    }

    public function getTokenTtl(): int
    {
        return (int) config('jwt.ttl', 60) * 60;
    }
}
