<?php

namespace Database\Factories;

use App\Models\Store;
use Illuminate\Database\Eloquent\Factories\Factory;

class StoreFactory extends Factory
{
    protected $model = Store::class;

    public function definition()
    {
        $filial = $this->faker->randomElement([
            ['city' => 'São Paulo', 'state' => 'SP', 'ddd' => '11'],
            ['city' => 'Campinas', 'state' => 'SP', 'ddd' => '19'],
            ['city' => 'Rio de Janeiro', 'state' => 'RJ', 'ddd' => '21'],
            ['city' => 'Belo Horizonte', 'state' => 'MG', 'ddd' => '31'],
            ['city' => 'Curitiba', 'state' => 'PR', 'ddd' => '41'],
            ['city' => 'Porto Alegre', 'state' => 'RS', 'ddd' => '51']
        ]);

        return [
            'name' => 'Nexus CD Hub - ' . $filial['city'],
            'city' => $filial['city'],
            'state' => $filial['state'],
            'phone' => '(' . $filial['ddd'] . ') ' . $this->faker->numerify('9####-####'),
        ];
    }
}