<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('sku')->unique();
            $table->string('color')->nullable();
            $table->string('size')->nullable(); // Ex: P, M, G, 42
            $table->decimal('weight', 8, 2)->nullable(); // Peso em kg
            $table->text('description')->nullable();
            $table->decimal('price_base', 10, 2); // Preço de custo/base do CD
            $table->integer('cd_stock_quantity')->default(0); // Estoque físico na Matriz
            
            // Status do CD: disponivel, em_transito, esgotado
            $table->string('status')->default('disponivel'); 
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('products');
    }
}
