<?php

namespace Database\Factories;

use App\Enums\ContactType;
use App\Models\Contact;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Contact>
 */
class ContactFactory extends Factory
{
    protected $model = Contact::class;

    public function definition(): array
    {
        $cities = [
            ['Panamá', 'Panamá'],
            ['Colón', 'Colón'],
            ['David', 'Chiriquí'],
            ['Chitré', 'Herrera'],
            ['Las Tablas', 'Los Santos'],
            ['Santiago', 'Veraguas'],
            ['Penonomé', 'Coclé'],
            ['La Chorrera', 'Panamá Oeste'],
            ['Puerto Armuelles', 'Chiriquí'],
            ['Aguadulce', 'Coclé'],
        ];

        [$city, $province] = fake()->randomElement($cities);
        $type = fake()->randomElement([ContactType::B2B, ContactType::B2C]);
        $company = $type === ContactType::B2B
            ? fake()->randomElement(['Grupo', 'Comercial', 'Industrias', 'Distribuidora', 'Estudio']).' '.fake()->lastName()
            : null;

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'phone' => '+507'.fake()->numerify('########'),
            'company' => $company,
            'job_title' => $type === ContactType::B2B
                ? fake()->randomElement(['Gerente de ventas', 'Directora de marketing', 'Analista de operaciones', 'Fundador', 'Jefa de compras'])
                : null,
            'contact_type' => $type,
            'business_type' => $type === ContactType::B2B
                ? fake()->randomElement(['Hotel', 'Proveedor', 'ONG', 'Restaurante', 'Retail'])
                : null,
            'gender' => fake()->randomElement(['Femenino', 'Masculino', 'Otro', 'Prefiere no decir']),
            'city' => $city,
            'province' => $province,
            'country' => 'Panamá',
            'birthdate' => fake()->dateTimeBetween('-65 years', '-20 years')->format('Y-m-d'),
            'source' => fake()->randomElement(['Feria', 'Referido', 'Web', 'Instagram', 'Evento', 'Importación CSV']),
            'associated_brand' => fake()->boolean(25) ? fake()->company() : null,
            'associated_company' => $type === ContactType::B2B && fake()->boolean(20) ? fake()->company() : null,
        ];
    }
}
