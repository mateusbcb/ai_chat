<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = \App\Models\Store::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $cidade = $this->faker->randomElement([
            ['city' => 'São Paulo', 'state' => 'SP'],
            ['city' => 'Campinas', 'state' => 'SP'],
            ['city' => 'Rio de Janeiro', 'state' => 'RJ'],
            ['city' => 'Niterói', 'state' => 'RJ'],
            ['city' => 'Belo Horizonte', 'state' => 'MG'],
            ['city' => 'Curitiba', 'state' => 'PR'],
            ['city' => 'Porto Alegre', 'state' => 'RS']
        ]);

        return [
            'name' => 'Nexus Franquia - ' . $cidade['city'],
            'city' => $cidade['city'],
            'state' => $cidade['state'],
        ];
    }
}
