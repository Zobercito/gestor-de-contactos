<?php

namespace Database\Factories;

use App\Enums\ImportStatus;
use App\Models\Import;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Import>
 */
class ImportFactory extends Factory
{
    protected $model = Import::class;

    public function definition(): array
    {
        $total = fake()->numberBetween(10, 200);
        $created = fake()->numberBetween(0, $total);

        return [
            'user_id' => User::factory(),
            'filename' => fake()->uuid().'.csv',
            'original_name' => 'contactos-'.fake()->monthName().'.csv',
            'status' => ImportStatus::Completed,
            'delimiter' => ',',
            'encoding' => 'UTF-8',
            'total_rows' => $total,
            'created_count' => $created,
            'skipped_count' => $total - $created,
            'duplicate_count' => fake()->numberBetween(0, $total - $created),
            'error_count' => 0,
            'mapping' => ['email' => 'Correo', 'name' => 'Nombre y Apellido'],
            'errors' => [],
            'started_at' => now()->subMinutes(5),
            'finished_at' => now(),
        ];
    }

    public function failed(): static
    {
        return $this->state(fn () => [
            'status' => ImportStatus::Failed,
            'error_count' => fake()->numberBetween(1, 10),
            'errors' => [['row' => 2, 'type' => 'error', 'message' => 'El archivo no tiene columnas reconocibles.']],
        ]);
    }
}
