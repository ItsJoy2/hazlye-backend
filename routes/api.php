<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\AuthController;
use App\Http\Controllers\API\CategoryController;
use App\Http\Controllers\API\ProductController;
use App\Http\Controllers\API\CartController;
use App\Http\Controllers\API\OrderController;
use App\Http\Controllers\API\CouponController;
use App\Http\Controllers\API\ReviewController;
use App\Http\Controllers\API\DeliveryOptionController;
use App\Http\Controllers\API\ColorController;
use App\Http\Controllers\API\SizeController;
use App\Http\Controllers\API\SearchController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes
// Route::post('/register', [AuthController::class, 'register']);
// Route::post('/login', [AuthController::class, 'login']);
Route::middleware(['throttle:20,1'])->group(function () {
// Categories
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{category}', [CategoryController::class, 'show']);
Route::get('/categories/{category}/products', [CategoryController::class, 'products']);

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/{product}', [ProductController::class, 'show']);
Route::get('/products/{product}/related', [ProductController::class, 'related']);

// Search
Route::get('/search', [SearchController::class, 'search']);

// Cart
Route::post('/cart', [CartController::class, 'store']);
Route::get('/cart/{session_id}', [CartController::class, 'show']);
Route::post('/cart/{session_id}/items', [CartController::class, 'addItem']);
Route::put('/cart/{session_id}/items/{item_id}', [CartController::class, 'updateItem']);
Route::delete('/cart/{session_id}/items/{item_id}', [CartController::class, 'removeItem']);

// Checkout
Route::post('/orders', [OrderController::class, 'store']);
Route::get('/orders', [OrderController::class, 'index']);
Route::get('/orders/{order}', [OrderController::class, 'show']);

// Coupons
Route::post('/coupons/validate', [CouponController::class, 'validate']);

// Reviews
Route::get('/products/{product}/reviews', [ReviewController::class, 'index']);
Route::post('/products/{product}/reviews', [ReviewController::class, 'store']);

// Delivery Options
Route::get('/delivery-options', [DeliveryOptionController::class, 'index']);

// Colors
Route::get('/colors', [ColorController::class, 'index']);

// Sizes
Route::get('/sizes', [SizeController::class, 'index']);

});