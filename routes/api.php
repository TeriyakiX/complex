<?php

use App\Http\Controllers\Admin\AdminStatsController;
use App\Http\Controllers\Admin\ProductImportController;
use App\Http\Controllers\ImportManufacturerController;
use App\Http\Controllers\MarketplaceController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\WarehouseController;
use App\Http\Controllers\WarehouseProductController;
use Illuminate\Http\Request;
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
Route::get('/reviews', [ReviewController::class, 'index']);         // Публичные отзывы
Route::post('/callback', [CallbackController::class, 'store']);    // Обратная связь
Route::post('/reviews', [ReviewController::class, 'store']); // Отправить отзыв
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

Route::prefix('warehouses')->group(function () {
    Route::get('/', [WarehouseController::class, 'index']);
    Route::get('/{warehouse}', [WarehouseController::class, 'show']);
});

Route::prefix('warehouses-products')->group(function () {
    Route::get('/{warehouseProduct}', [WarehouseProductController::class, 'show']);
});

Route::prefix('marketplaces')->group(function () {
    Route::get('/', [MarketplaceController::class, 'index']);
});

Route::prefix('orders')->group(function () {
    Route::post('/', [OrderController::class, 'store']);
});

Route::get('/sitemap.xml', function (Request $request) {
    $response = response()->file(public_path('sitemaps/sitemap-index.xml'));

    // Добавляем заголовки CORS
    $response->headers->set('Access-Control-Allow-Origin', '*');
    $response->headers->set('Access-Control-Allow-Methods', 'GET, OPTIONS');
    $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization');

    return $response;
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

    Route::prefix('admin')->group(function () {

        Route::middleware('can:is-admin')->group(function () {
            Route::put('/reviews/{review}/{status}', [ReviewController::class, 'changeStatus']);
            Route::get('/reviews', [ReviewController::class, 'all']);

            Route::prefix('orders')->group(function () {
                Route::get('/', [OrderController::class, 'index']);
                Route::get('/{order}', [OrderController::class, 'show']);
                Route::put('/{order}/{status}', [OrderController::class, 'updateStatus']);
            });

            Route::prefix('warehouses')->group(function () {
                Route::get('/', [WarehouseController::class, 'index']);
                Route::get('/{warehouse}', [WarehouseController::class, 'show']);
                Route::post('/{warehouse}/upload-image', [WarehouseController::class, 'uploadImage']);
                Route::post('/{warehouse}/import', [WarehouseController::class, 'import']);
            });

            Route::prefix('products')->group(function () {
                Route::get('/', [ProductController::class, 'index']);
                Route::get('/{product}', [ProductController::class, 'show']);
                Route::post('/', [ProductController::class, 'store']);
                Route::put('/{product}', [ProductController::class, 'update']);
                Route::delete('/{product}', [ProductController::class, 'destroy']);
                Route::post('/import', [ProductImportController::class, 'import']);
                Route::get('/import/status/{id}', [ProductImportController::class, 'importStatus']);
            });

            Route::prefix('manufacturers')->group(function () {
                Route::get('/', [ManufacturerController::class, 'index']);
                Route::get('/{manufacturer}', [ManufacturerController::class, 'show']);
                Route::post('/', [ManufacturerController::class, 'store']);
                Route::put('/{manufacturer}', [ManufacturerController::class, 'update']);
                Route::delete('/{manufacturer}', [ManufacturerController::class, 'destroy']);
                Route::post('/import', [ImportManufacturerController::class, 'import']);
            });

            Route::prefix('marketplaces')->group(function () {
                Route::get('/', [MarketplaceController::class, 'index']);
                Route::get('/{marketplace}', [MarketplaceController::class, 'show']);
                Route::post('/', [MarketplaceController::class, 'store']);
                Route::put('/{marketplace}', [MarketplaceController::class, 'update']);
                Route::delete('/{marketplace}', [MarketplaceController::class, 'destroy']);
                Route::post('/import', [MarketplaceImportController::class, 'import']);
            });


            Route::prefix('callback')->group(function () {
                Route::get('/', [CallbackController::class, 'index']);
                Route::get('/{id}', [CallbackController::class, 'show']);
                Route::delete('/{id}', [CallbackController::class, 'destroy']);
                Route::put('/{id}/{status}', [CallbackController::class, 'updateStatus']);
            });

            Route::get('/stats', [AdminStatsController::class, 'index']);

        });


    });
});
