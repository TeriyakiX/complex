<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExportStatus extends Model
{
    protected $fillable = ['file_name', 'status', 'message'];
}
