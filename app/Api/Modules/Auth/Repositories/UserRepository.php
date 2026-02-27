<?php

declare(strict_types=1);

namespace App\Api\Modules\Auth\Repositories;

use App\Models\User;

class UserRepository
{
    /** @param  array<string, mixed>  $data */
    public function create(array $data): User
    {
        return User::query()->create($data);
    }
}
