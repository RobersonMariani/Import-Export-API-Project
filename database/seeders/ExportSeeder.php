<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Export;
use App\Models\User;
use Illuminate\Database\Seeder;

class ExportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users->random(min(8, $users->count())) as $user) {
            Export::factory()->completed()->create([
                'user_id' => $user->id,
                'total_records' => fake()->numberBetween(100, 50000),
                'compressed' => fake()->boolean(70),
                'filters' => fake()->randomElement([
                    null,
                    json_encode(['role' => 'user']),
                    json_encode(['state' => 'SP']),
                    json_encode(['search' => 'João']),
                    json_encode(['role' => 'admin', 'state' => 'RJ', 'city' => 'Rio de Janeiro']),
                ]),
            ]);
        }

        foreach ($users->random(min(2, $users->count())) as $user) {
            Export::factory()->processing()->create([
                'user_id' => $user->id,
                'compressed' => fake()->boolean(),
            ]);
        }

        foreach ($users->random(min(3, $users->count())) as $user) {
            Export::factory()->queued()->create([
                'user_id' => $user->id,
            ]);
        }

        foreach ($users->random(min(1, $users->count())) as $user) {
            Export::factory()->failed()->create([
                'user_id' => $user->id,
            ]);
        }
    }
}
