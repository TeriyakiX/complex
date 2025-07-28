<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'name',
        'phone',
        'email',
        'product_id',
        'warehouse_product_id',
        'quantity',
        'text',
        'status',
    ];


    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function warehouseProduct()
    {
        return $this->belongsTo(WarehouseProduct::class, 'warehouse_product_id');
    }
}
