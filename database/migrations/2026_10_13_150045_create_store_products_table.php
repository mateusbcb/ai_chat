<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_products', function (Blueprint $table) {
            $table->id();
            // Relacionamentos indexados e seguros com cascade
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->integer('quantity')->default(0);
            $table->decimal('price_sale_suggested', 10, 2)->nullable(); // Preço customizado da filial
            
            // Status local: disponivel, em_transito, a_venda
            $table->string('status')->default('disponivel'); 
            
            $table->timestamps(); // Data/Hora exata da movimentação logística na loja
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_products');
    }
}
