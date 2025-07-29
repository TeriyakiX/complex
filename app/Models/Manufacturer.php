<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model
{
    use HasUuid;

    protected $appends = ['total_products_count'];

    protected $fillable = [
        'name',
        'image',
    ];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function warehouseProducts()
    {
        return $this->hasMany(WarehouseProduct::class);
    }

    public function scopeWithImagePriorityAndSortedName($query)
    {
        return $query
            ->orderByRaw('CASE WHEN products_count > 0 THEN 0 ELSE 1 END')
            ->orderByRaw("CASE WHEN image IS NOT NULL AND image != '' THEN 0 ELSE 1 END")
            ->orderBy('name');
    }

    public function getTotalProductsCountAttribute(): int
    {
        return ($this->products_count ?? 0) + ($this->warehouse_products_count ?? 0);
    }
}
