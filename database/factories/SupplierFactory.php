<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SupplierFactory extends Factory
{
    protected $model = \App\Models\Supplier::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->company() . ' Distribuidora',
            'cnpj' => $this->faker->numerify('##.###.###/0001-##'), // Gera um CNPJ fake estruturado
            'contact_email' => $this->faker->unique()->safeEmail(),
        ];
    }
}
