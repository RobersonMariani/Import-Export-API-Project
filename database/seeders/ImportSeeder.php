<?php

namespace Database\Seeders;

use App\Models\Import;
use App\Models\ImportFailure;
use App\Models\User;
use Illuminate\Database\Seeder;

class ImportSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();

        if ($users->isEmpty()) {
            return;
        }

        foreach ($users->random(min(10, $users->count())) as $user) {
            Import::factory()->completed()->create([
                'user_id' => $user->id,
                'total_records' => $total = fake()->numberBetween(100, 5000),
                'success_count' => $success = (int) ($total * fake()->randomFloat(2, 0.90, 1.0)),
                'failure_count' => $total - $success,
                'progress' => $total,
            ]);
        }

        foreach ($users->random(min(3, $users->count())) as $user) {
            Import::factory()->processing()->create([
                'user_id' => $user->id,
                'total_records' => $total = fake()->numberBetween(500, 10000),
                'success_count' => $processed = (int) ($total * fake()->randomFloat(2, 0.2, 0.7)),
                'progress' => $processed,
            ]);
        }

        foreach ($users->random(min(5, $users->count())) as $user) {
            Import::factory()->queued()->create([
                'user_id' => $user->id,
            ]);
        }

        foreach ($users->random(min(2, $users->count())) as $user) {
            $import = Import::factory()->failed()->create([
                'user_id' => $user->id,
                'total_records' => fake()->numberBetween(100, 1000),
                'failure_count' => fake()->numberBetween(10, 50),
            ]);

            $failureCount = min($import->failure_count, 10);
            for ($i = 1; $i <= $failureCount; $i++) {
                ImportFailure::create([
                    'import_id' => $import->id,
                    'line_number' => $i * fake()->numberBetween(1, 100),
                    'payload' => json_encode([
                        'name' => fake()->name(),
                        'email' => 'invalid-email',
                    ]),
                    'error_message' => fake()->randomElement([
                        'O campo email deve ser um endereço de e-mail válido.',
                        'O campo password é obrigatório.',
                        'O campo name não pode ter mais que 255 caracteres.',
                        'Registro duplicado para o e-mail informado.',
                    ]),
                ]);
            }
        }
    }
}
