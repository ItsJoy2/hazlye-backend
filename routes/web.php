<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\AdminCategoryController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminDeliveryOptionController;
use App\Http\Controllers\Admin\AdminColorController;
use App\Http\Controllers\Admin\AdminSizeController;
use App\Http\Controllers\Admin\AdminReviewController;
use App\Http\Controllers\Admin\GeneralSettingsController;

Route::get('/', function () {
    return redirect('/signin');
});

// Login/Logout Routes
Route::get('signin', [AuthController::class, 'index'])->name('admin.login');
Route::post('signin', [AuthController::class, 'login'])->name('admin.login.submit');
Route::post('logout', [AuthController::class, 'logout'])->name('admin.logout');

// Protect Admin Routes
Route::prefix('admin')->middleware(['auth:admin'])->group(function () {

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');

    // Categories
    Route::resource('categories', AdminCategoryController::class)->names([
        'index' => 'admin.categories.index',
        'create' => 'admin.categories.create',
        'store' => 'admin.categories.store',
        'show' => 'admin.categories.show',
        'edit' => 'admin.categories.edit',
        'update' => 'admin.categories.update',
        'destroy' => 'admin.categories.destroy'
    ]);

    // Products
    Route::resource('products', AdminProductController::class)->names([
        'index' => 'admin.products.index',
        'create' => 'admin.products.create',
        'store' => 'admin.products.store',
        'show' => 'admin.products.show',
        'edit' => 'admin.products.edit',
        'update' => 'admin.products.update',
        'destroy' => 'admin.products.destroy'
    ]);
    Route::delete('products/images/{image}', [AdminProductController::class, 'deleteImage'])->name('admin.products.images.destroy');
    Route::get('admin/products/variant', [AdminProductController::class, 'variant'])->name('admin.products.variant');

    Route::get('admin/products/option', [AdminProductController::class, 'option'])->name('admin.products.option');




    // Orders
    Route::resource('orders', AdminOrderController::class)->except(['store', 'create', 'edit']);
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus']);

    // Coupons
    Route::resource('coupons', AdminCouponController::class)->except(['create', 'edit']);

    // Delivery Options
    Route::resource('delivery-options', AdminDeliveryOptionController::class)->except(['create', 'edit']);

    // Colors
    Route::resource('colors', AdminColorController::class)->names([
        'index' => 'admin.colors.index',
        'create' => 'admin.colors.create',
        'store' => 'admin.colors.store',
        'show' => 'admin.colors.show',
        'edit' => 'admin.colors.edit',
        'update' => 'admin.colors.update',
        'destroy' => 'admin.colors.destroy'
    ]);
;

    // Sizes
    Route::resource('sizes', AdminSizeController::class)->names([
        'index' => 'admin.sizes.index',
        'create' => 'admin.sizes.create',
        'store' => 'admin.sizes.store',
        'show' => 'admin.sizes.show',
        'edit' => 'admin.sizes.edit',
        'update' => 'admin.sizes.update',
        'destroy' => 'admin.sizes.destroy'
    ]);;

    // Reviews
    Route::resource('reviews', AdminReviewController::class)->except(['store', 'create', 'edit']);
    Route::put('reviews/{review}/approve', [AdminReviewController::class, 'approve']);

    // General Settings
    Route::get('general-settings', [GeneralSettingsController::class, 'index'])->name('admin.general.settings');
    Route::post('general-settings', [GeneralSettingsController::class, 'update'])->name('admin.general.settings.update');

});
