<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseProduct extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'manufacturer_id', 'stock', 'warehouse_id'];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function manufacturer()
    {
        return $this->belongsTo(Manufacturer::class);
    }

}
