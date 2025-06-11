<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PasswordReset extends Model
{
    public $timestamps = false;

    protected $fillable = ['email', 'code', 'created_at'];

    protected static function booted(): void
    {
        static::creating(function ($model) {
            $model->id = Str::uuid();
        });
    }
}
