<?php

namespace Database\Factories;

use App\Models\Segment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Segment>
 */
class SegmentFactory extends Factory
{
    protected $model = Segment::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->randomElement([
                'Clientes',
                'Proveedores',
                'Prensa',
                'Eventos',
                'Hoteles',
                'Restaurantes',
                'ONG',
                'Referidos',
                'Newsletter',
                'Prioridad alta',
            ]),
            'color' => fake()->randomElement(['#C65D3B', '#7A8B6F', '#D9A441', '#8C6A9E', '#4F7C8A', '#B56576']),
            'description' => fake()->boolean(60) ? fake()->sentence(8) : null,
        ];
    }
}
