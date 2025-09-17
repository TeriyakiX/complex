<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    use HasUuid;
    protected $fillable = ['name', 'description', 'manufacturer_id'];

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }
    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class, 'warehouse_products');
    }
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($product) {
            if (empty($product->slug)) {
                $base = $product->name . ' ' . ($product->description ?? '');
                $product->slug = Str::slug($base . '-' . substr($product->id, 0, 8));
            }
        });

        static::updating(function ($product) {
            if (empty($product->slug)) {
                $base = $product->name . ' ' . ($product->description ?? '');
                $product->slug = Str::slug($base . '-' . substr($product->id, 0, 8));
            }
        });
    }
}
