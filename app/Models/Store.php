<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Store extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'city', 'state'];

    /**
     * Relacionamento: Uma franquia possui vários produtos em seu estoque.
     * Também recupera a coluna extra 'stock_quantity'.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_store')
                    ->withPivot('stock_quantity')
                    ->withTimestamps();
    }
}
