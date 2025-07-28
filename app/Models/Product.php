<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
