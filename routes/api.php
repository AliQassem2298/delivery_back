<?php

use App\Http\Controllers\AddressController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\MarketController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use Illuminate\Http\Request;
use App\Http\Controllers\AmountController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post("/register", [UserController::class, 'register']);
Route::post("/login", [UserController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post("/logout", [UserController::class, 'logout']);
    Route::get("/ShowUserProfile", [UserController::class, 'ShowUserProfile']);
    Route::post("/UpdateProfile", [UserController::class, 'UpdateProfile']);
});


Route::controller(ImageController::class)->group(function () {
    Route::post('images/upload', 'upload');
    Route::get('images', 'index');
    Route::middleware('auth:sanctum')->post('/user/profile-image', 'updateProfileImage');
});


Route::controller(AddressController::class)->middleware('auth:sanctum')->group(function () {
    Route::post('/address/update', 'updateAddress');
});

Route::get('/getProductAmounts/{product_id}', [\App\Http\Controllers\AmountController::class, 'getProductAmounts']);

Route::controller(MarketController::class)->group(function () {
    Route::get('/show_all_markets', 'show_all_markets');
    Route::post('/search_market', 'search_Market');
});

Route::controller(ProductController::class)->group(function () {
    Route::get('/show_products/{market_id}', 'show_products');
    Route::get('/show_product/{id}', 'show_product');
    Route::get('/show_all_products', 'show_all_products');
    Route::post('/search_product', 'search_product');
});


Route::middleware('auth:sanctum')->group(
    function () {
        Route::controller(FavoriteController::class)->group(function () {
            Route::post('add/favorite/{product_id}', 'add_to_Favorite');
            Route::get('favorite', 'showFavorites');
            Route::post('remove/favorites/{product_id}', 'remove_from_Favorite');
        });
    }
);


Route::middleware('auth:sanctum')->group(function () {
    Route::controller(OrderController::class)->group(function () {
        Route::post('/add_to_order/{product_id}', 'add_to_order');
        Route::get('/show_order', 'show_order');
        Route::post('/cancelOrder/{id}', 'cancelOrder');
        Route::post('/modifyOrder/{id}', 'modifyOrder');
    });
    Route::middleware('auth:sanctum')
        ->get('/notifications', [NotificationController::class, 'getUserNotifications']);


});
Route::middleware('auth:sanctum')->group(function () {
    Route::get('search', [SearchController::class, 'search']);
});
