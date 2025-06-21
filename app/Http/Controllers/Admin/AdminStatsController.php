<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\User;
use Illuminate\Http\Request;

class AdminStatsController extends Controller
{
    public function index()
    {
        return [
            'message' => 'Статистика успешно получена',
            'data' => [
                'users_total'       => User::count(),
                'reviews_approved'  => Review::where('status', 'approved')->count(),
                'reviews_pending'   => Review::where('status', 'pending')->count(),
            ],
        ];
    }
}
