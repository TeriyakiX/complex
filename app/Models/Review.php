<?php

namespace App\Models;

use App\Traits\HasUuid;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasUuid;
    protected $fillable = [
        'name',
        'email',
        'text',
        'rating',
        'status',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
