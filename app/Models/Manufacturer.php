<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Manufacturer extends Model

{
    use HasUuid;
    protected $fillable = ['name', 'image'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeWithImagePriorityAndSortedName($query)
    {
        return $query
            ->orderByRaw('CASE WHEN products_count > 0 THEN 0 ELSE 1 END')
            ->orderByRaw("CASE WHEN image IS NOT NULL AND image != '' THEN 0 ELSE 1 END")
            ->orderByRaw("CASE WHEN LEFT(name, 1) REGEXP '^[0-9]' THEN 1 ELSE 0 END")
            ->orderBy('name');
    }
}
