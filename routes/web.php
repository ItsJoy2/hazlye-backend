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
use App\Http\Controllers\Admin\HomepageSectionController;
use App\Http\Controllers\CkeditorController;

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

      // For profile route
      Route::get('/profile', [AuthController::class, 'profileEdit'])->name('admin.profile');
      Route::post('/profile', [AuthController::class, 'profileUpdate'])->name('admin.profile.update');

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

    //Homepage section
    Route::resource('homepage-sections', HomepageSectionController::class)->names([
        'index' => 'admin.homepage-sections.index',
        'create' => 'admin.homepage-sections.create',
        'store' => 'admin.homepage-sections.store',
        'show' => 'admin.homepage-sections.show',
        'edit' => 'admin.homepage-sections.edit',
        'update' => 'admin.homepage-sections.update',
        'destroy' => 'admin.homepage-sections.destroy'
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
    Route::resource('orders', AdminOrderController::class)->names([
       'index' => 'admin.orders.index',
        'show' => 'admin.orders.show',
        'edit' => 'admin.orders.edit',
        'update' => 'admin.orders.update',
    ]);
    Route::put('orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('admin.orders.update-status');
    Route::get('admin/orders/download/{order}', [AdminOrderController::class, 'download'])->name('admin.orders.download');

    Route::get('/customers', [AdminOrderController::class, 'customerList'])->name('admin.customers.index');

    // Coupons
    Route::resource('coupons', AdminCouponController::class)->names([
        'index' => 'admin.coupons.index',
        'create' => 'admin.coupons.create',
        'store' => 'admin.coupons.store',
        'edit' => 'admin.coupons.edit',
        'update' => 'admin.coupons.update',
        'destroy' => 'admin.coupons.destroy'
    ]);
    Route::get('coupons/search-products', [AdminCouponController::class, 'searchProducts'])
    ->name('admin.coupons.search-products');

    // Delivery Options
    Route::resource('delivery-options', AdminDeliveryOptionController::class)->names([
        'index' => 'admin.delivery-options.index',
        'create' => 'admin.delivery-options.create',
        'store' => 'admin.delivery-options.store',
        'show' => 'admin.delivery-options.show',
        'edit' => 'admin.delivery-options.edit',
        'update' => 'admin.delivery-options.update',
        'destroy' => 'admin.delivery-options.destroy'
    ]);

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
    Route::get('social-links', [GeneralSettingsController::class, 'socialLinks'])->name('admin.socials.links');


    // CKE Editor
    Route::post('ckeditor/upload', [CKEditorController::class, 'upload'])->name('ckeditor.upload');
});
