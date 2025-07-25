<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;


class Warehouse extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = ['name', 'image'];

    public function products()
    {
        return $this->hasMany(WarehouseProduct::class);
    }
}
