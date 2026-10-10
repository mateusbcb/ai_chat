<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'sku', 'price', 'description'];

    /**
     * Relacionamento: Um produto pode vir de vários fornecedores.
     */
    public function suppliers()
    {
        return $this->belongsToMany(Supplier::class, 'product_supplier');
    }

    /**
     * Relacionamento: Um produto pode estar presente em várias lojas.
     * Carrega junto a coluna extra 'stock_quantity' da tabela pivô.
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'product_store')
                    ->withPivot('stock_quantity')
                    ->withTimestamps(); // Caso queira rastrear quando o estoque mudou
    }
}
