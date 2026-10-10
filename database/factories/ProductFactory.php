<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
     protected $model = \App\Models\Product::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'title' => $this->faker->randomElement(['Camiseta', 'Calça', 'Tênis', 'Moletom', 'Boné', 'Meias']) . ' ' . $this->faker->colorName(),
            'sku' => strtoupper($this->faker->unique()->lexify('NEX-????-')) . $this->faker->numberBetween(1000, 9999),
            'price' => $this->faker->randomFloat(2, 29, 299), // Preços entre R$29,00 e R$299,00
            'description' => $this->faker->sentence(10),
        ];
    }
}
