<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = Product::class;

    public function definition()
    {
        $custo = $this->faker->randomFloat(2, 15, 120);

        return [
            'title' => $this->faker->randomElement(['Jaqueta', 'Calça Moletom', 'Tênis Running', 'Camiseta DryFit', 'Moletom Canguru', 'Boné Strapback']) . ' ' . $this->faker->colorName(),
            'sku' => strtoupper($this->faker->unique()->lexify('NEX-WMS-????')) . $this->faker->numberBetween(10, 99),
            'color' => $this->faker->colorName(),
            'size' => $this->faker->randomElement(['P', 'M', 'G', 'GG', '38', '40', '42']),
            'weight' => $this->faker->randomFloat(2, 0.15, 2.5), // Peso em kg
            'description' => $this->faker->sentence(8),
            'price_base' => $custo,
            'cd_stock_quantity' => $this->faker->numberBetween(100, 1500), // Estoque robusto no CD pulmão
            'status' => $this->faker->randomElement(['disponivel', 'disponivel', 'em_transito', 'esgotado']),
        ];
    }
}