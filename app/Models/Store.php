<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $table = 'stores';

    protected $fillable = ['name', 'city', 'state', 'phone'];

    /**
     * Relacionamento: Uma loja possui vários produtos em seu estoque local.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'store_products')
                    ->withPivot(['id', 'quantity', 'price_sale_suggested', 'status'])
                    ->withTimestamps();
    }

    /**
     * Relacionamento: Uma loja possui movimentações de estoque registradas.
     */
    public function storeProducts()
    {
        return $this->hasMany(StoreProduct::class);
    }

    /**
     * Relacionamento: Uma loja emite múltiplos cupons de faturamento/vendas.
     */
    public function sales()
    {
        return $this->hasMany(StoreSale::class);
    }
}
