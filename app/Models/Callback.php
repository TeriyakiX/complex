<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Callback extends Model
{
    use HasUuid;
    protected $fillable = ['name', 'phone','email', 'text','agree', 'status'];

    protected $attributes = [
        'status' => 'pending',
    ];
}
