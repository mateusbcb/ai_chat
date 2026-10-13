<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStoreSalesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('store_sales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id')->constrained()->onDelete('cascade');
            $table->foreignId('product_id')->constrained()->onDelete('cascade');
            
            $table->decimal('price_sale', 10, 2); // Preço real praticado no momento do cupom fiscal
            $table->integer('quantity');
            
            // Status da venda: vendido, devolvido, quebra
            $table->string('status')->default('vendido'); 
            
            $table->timestamps(); // Rastreabilidade do segundo exato da venda/estorno
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('store_sales');
    }
}
