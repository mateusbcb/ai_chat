<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreSale extends Model
{
    use HasFactory;

    protected $table = 'store_sales';

    protected $fillable = ['store_id', 'product_id', 'price_sale', 'quantity', 'status'];

    public function store()
    {
        return $this->belongsTo(Store::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
