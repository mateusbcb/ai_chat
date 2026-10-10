<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        // 1. Cria 10 Fornecedores fakes
        $suppliers = Supplier::factory()->count(10)->create();

        // 2. Cria 5 Lojas/Franquias fakes
        $stores = Store::factory()->count(5)->create();

        // 3. Cria 30 Produtos fakes e gerencia os relacionamentos
        Product::factory()->count(30)->create()->each(function ($product) use ($suppliers, $stores) {
            
            // Sorteia de 1 a 3 fornecedores aleatórios e vincula
            $randomSuppliers = $suppliers->random(rand(1, 3))->pluck('id');
            $product->suppliers()->attach($randomSuppliers);

            // Sorteia de 2 a 4 lojas aleatórias
            $randomStores = $stores->random(rand(2, 4));
            
            // Prepara um array formatado para o Eloquent salvar em lote com os dados do pivot
            $storesData = [];
            foreach ($randomStores as $store) {
                $storesData[$store->id] = [
                    'stock_quantity' => rand(10, 150)
                ];
            }

            // O método attach() do Eloquent preencherá os timestamps automaticamente!
            $product->stores()->attach($storesData);
        });
    }
}
