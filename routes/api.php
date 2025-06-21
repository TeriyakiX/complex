<?php

use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\ImportManufacturerController;
use App\Http\Controllers\MarketplaceController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ManufacturerController;
use App\Http\Controllers\ProductController;
use \App\Http\Controllers\Admin\MarketplaceImportController;



/*
|--------------------------------------------------------------------------
| AUTH
|--------------------------------------------------------------------------
*/
Route::prefix('auth')->middleware('api')->group(function () {
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
Route::prefix('password')->middleware('api')->group(function () {
    Route::post('/send-code', [PasswordResetController::class, 'sendResetCode']); // Отправка кода
    Route::post('/reset', [PasswordResetController::class, 'resetPassword']);     // Сброс пароля
    Route::post('/check-code', [PasswordResetController::class, 'checkCode']);    // Проверка кода
});

/*
|--------------------------------------------------------------------------
| PUBLIC REVIEWS & CALLBACK
|--------------------------------------------------------------------------
*/
Route::middleware('api')->group(function () {
    Route::get('/reviews', [ReviewController::class, 'index']);         // Публичные отзывы
    Route::post('/callback', [CallbackController::class, 'store']);    // Обратная связь
});

/*
|--------------------------------------------------------------------------
| PUBLIC PRODUCTS
|--------------------------------------------------------------------------
*/
Route::prefix('products')->middleware('api')->group(function () {
    Route::get('/', [ProductController::class, 'index']);
    Route::get('/{product}', [ProductController::class, 'show']);
});
Route::prefix('manufacturers')->group(function () {
    Route::get('/', [ManufacturerController::class, 'index']);
    Route::get('/{manufacturer}', [ManufacturerController::class, 'show']);
});

Route::prefix('marketplaces')->group(function () {
    Route::get('/', [MarketplaceController::class, 'index']);
});

/*
|--------------------------------------------------------------------------
| AUTH-ONLY ROUTES
|--------------------------------------------------------------------------
*/
Route::middleware(['api', 'auth:api'])->group(function () {

    /*
    |--------------------------------------------------------------------------
    | USER REVIEWS
    |--------------------------------------------------------------------------
    */
    Route::post('/reviews', [ReviewController::class, 'store']); // Отправить отзыв



    Route::prefix('admin')->group(function () {

        Route::middleware('can:is-admin')->group(function () {
            Route::post('/reviews/{review}/approve', [ReviewController::class, 'approve']); // Одобрение отзыва
            Route::get('/reviews', [ReviewController::class, 'all']);// Все отзывы (админ)


            Route::prefix('products')->group(function () {
                Route::post('/', [ProductController::class, 'store']);
                Route::put('/{product}', [ProductController::class, 'update']);
                Route::delete('/{product}', [ProductController::class, 'destroy']);
            });

            Route::prefix('manufacturers')->group(function () {
                Route::post('/', [ManufacturerController::class, 'store']);
                Route::put('/{manufacturer}', [ManufacturerController::class, 'update']);
                Route::delete('/{manufacturer}', [ManufacturerController::class, 'destroy']);
                Route::post('/import', [ImportManufacturerController::class, 'import']);
            });


            Route::post('/marketplaces/import', [MarketplaceImportController::class, 'import']);
            Route::get('/stats', [AdminStatsController::class, 'index']);
        });


    });
});
