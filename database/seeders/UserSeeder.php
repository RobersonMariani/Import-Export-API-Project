<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::factory()->admin()->withProfile()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
        ]);

        User::factory()->manager()->withProfile()->create([
            'name' => 'Manager User',
            'email' => 'manager@example.com',
        ]);

        User::factory()->withProfile()->create([
            'name' => 'Regular User',
            'email' => 'user@example.com',
        ]);

        User::factory(5)->admin()->withProfile()->create();
        User::factory(10)->manager()->withProfile()->create();
        User::factory(50)->withProfile()->create();
        User::factory(30)->create();
    }
}
