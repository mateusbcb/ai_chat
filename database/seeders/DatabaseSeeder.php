<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Store;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Executa o populador de banco de dados do WMS Logístico da Nexus.
     */
    public function run()
    {
        // 1. Cria 5 Franquias/Lojas físicas regionais
        $stores = Store::factory()->count(5)->create();

        // 2. Cria 40 Produtos robustos estocados no CD pulmão
        Product::factory()->count(40)->create()->each(function ($product) use ($stores) {
            
            // Sorteia de 2 a 4 lojas aleatórias para receberem remessas desse produto
            $randomStores = $stores->random(rand(2, 4));

            foreach ($randomStores as $store) {
                // Simula uma margem de lucro de varejo regional (Preço Base + 40% a 80%)
                $precoVendaSugerido = $product->price_base * rand(140, 180) / 100;
                
                // Define o status do lote nessa filial
                $statusLogistico = rand(1, 10) > 8 ? 'em_transito' : 'a_venda';

                // Injeta no estoque físico local da filial (Tabela Pivô) com timestamps
                DB::table('store_products')->insert([
                    'store_id' => $store->id,
                    'product_id' => $product->id,
                    'quantity' => rand(20, 120),
                    'price_sale_suggested' => round($precoVendaSugerido, 2),
                    'status' => $statusLogistico,
                    'created_at' => now()->subDays(rand(1, 15)), // Simula datas retroativas de envio
                    'updated_at' => now(),
                ]);

                // Simula o histórico de vendas/movimentações no caixa para esse produto nessa loja
                $quantidadeVendas = rand(1, 4);
                for ($i = 0; $i < $quantidadeVendas; $i++) {
                    
                    // Sorteia o status do cupom: vendido (80%), devolvido (10%) ou quebra/perda (10%)
                    $sorteioStatus = rand(1, 10);
                    if ($sorteioStatus <= 8) {
                        $statusVenda = 'vendido';
                        $precoFinalPraticado = round($precoVendaSugerido * rand(95, 100) / 100, 2); // Simula pequenos descontos
                    } elseif ($sorteioStatus == 9) {
                        $statusVenda = 'devolvido';
                        $precoFinalPraticado = round($precoVendaSugerido, 2);
                    } else {
                        $statusVenda = 'quebra';
                        $precoFinalPraticado = 0.00; // Quebra representa perda total física do produto
                    }

                    DB::table('store_sales')->insert([
                        'store_id' => $store->id,
                        'product_id' => $product->id,
                        'price_sale' => $precoFinalPraticado,
                        'quantity' => rand(1, 5),
                        'status' => $statusVenda,
                        'created_at' => now()->subDays(rand(1, 10))->subHours(rand(1, 12)),
                        'updated_at' => now(),
                    ]);
                }
            }
        });
    }
}