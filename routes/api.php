<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);   // Регистрация
    Route::post('/login', [AuthController::class, 'login']);         // Логин

    Route::middleware('auth:api')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);            // Инфо о себе
        Route::post('/logout', [AuthController::class, 'logout']);  // Выход
        Route::post('/refresh', [AuthController::class, 'refresh']); // Обновление токена
    });
});

/*
|--------------------------------------------------------------------------
| PASSWORD RESET
|--------------------------------------------------------------------------
*/
Route::prefix('password')->group(function () {
    Route::post('/send-code', [PasswordResetController::class, 'sendResetCode']); // Отправка кода
    Route::post('/reset', [PasswordResetController::class, 'resetPassword']);     // Сброс пароля
});

/*
|--------------------------------------------------------------------------
| PUBLIC REVIEWS & CALLBACK
|--------------------------------------------------------------------------
*/
Route::get('/reviews', [ReviewController::class, 'index']);            // Публичные отзывы
Route::post('/callback', [CallbackController::class, 'store']);       // Обратная связь

/*
|--------------------------------------------------------------------------
| AUTH-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware('auth:api')->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER REVIEWS
    |--------------------------------------------------------------------------
    */
    Route::post('/reviews', [ReviewController::class, 'store']); // Отправить отзыв

    Route::middleware('can:is-admin')->group(function () {
        Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve']); // Одобрение отзыва
        Route::get('/admin/reviews', [ReviewController::class, 'all']);                 // Все отзывы (админ)
    });

    /*
    |--------------------------------------------------------------------------
    | MANUFACTURERS CRUD
    |--------------------------------------------------------------------------
    */
    Route::prefix('manufacturers')->group(function () {
        Route::get('/', [ManufacturerController::class, 'index']);
        Route::post('/', [ManufacturerController::class, 'store']);
        Route::get('/{manufacturer}', [ManufacturerController::class, 'show']);
        Route::put('/{manufacturer}', [ManufacturerController::class, 'update']);
        Route::delete('/{manufacturer}', [ManufacturerController::class, 'destroy']);
    });

    /*
    |--------------------------------------------------------------------------
    | PRODUCTS CRUD
    |--------------------------------------------------------------------------
    */
    Route::prefix('products')->group(function () {
        Route::get('/', [ProductController::class, 'index']);
        Route::post('/', [ProductController::class, 'store']);
        Route::get('/{product}', [ProductController::class, 'show']);
        Route::put('/{product}', [ProductController::class, 'update']);
        Route::delete('/{product}', [ProductController::class, 'destroy']);
    });
});
