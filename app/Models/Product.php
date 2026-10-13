<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;
    
    protected $table = 'products';

    protected $fillable = ['title','sku','color','size','weight', 'description','price_base','cd_stock_quantity','status' ];

    /**
     * Relacionamento: Um produto pode estar estocado em várias lojas/franquias.
     */
    public function stores()
    {
        return $this->belongsToMany(Store::class, 'store_products')
                    ->withPivot(['id', 'quantity', 'price_sale_suggested', 'status'])
                    ->withTimestamps();
    }

    /**
     * Relacionamento: Um produto possui múltiplos históricos de movimentação direta.
     */
    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }

    /**
     * Relacionamento: Um produto acumula vários registros de cupons fiscais/vendas.
     */
    public function sales()
    {
        return $this->hasMany(StoreSale::class);
    }
}
