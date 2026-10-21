<?php

use App\Http\Controllers\Admin;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CatalogController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderPaymentProofController;
use Illuminate\Support\Facades\Route;

Route::get('/', [CatalogController::class, 'index'])->name('home');
Route::get('/menu', [CatalogController::class, 'legacy'])->name('catalog.index');
Route::get('/menu/{product}', [CatalogController::class, 'show'])->name('products.show');

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'loginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:login')->name('login.store');
    Route::get('/register', [AuthController::class, 'registerForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->middleware('throttle:register')->name('register.store');
});

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/{product}', [CartController::class, 'store'])->block(10, 10)->name('cart.store');
Route::patch('/cart/{item}', [CartController::class, 'update'])->block(10, 10)->name('cart.update');
Route::delete('/cart/{item}', [CartController::class, 'destroy'])->block(10, 10)->name('cart.destroy');

Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/checkout', [CheckoutController::class, 'show'])->block(10, 10)->name('checkout.show');
    Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->block(10, 10)->name('checkout.store');
    Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/orders/{order}/payment-proof', [OrderPaymentProofController::class, 'store'])->name('orders.payment_proof');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', Admin\DashboardController::class)->name('dashboard');
        Route::resource('categories', Admin\CategoryController::class)->except('show');
        Route::resource('products', Admin\ProductController::class)->except('show');
        Route::post('products/{product}/restore', [Admin\ProductController::class, 'restore'])->withTrashed()->name('products.restore');
        Route::resource('addons', Admin\AddonController::class)->except('show');
        Route::resource('orders', Admin\OrderController::class)->only('index', 'show', 'update');
        Route::post('orders/{order}/payment-status', [Admin\OrderController::class, 'updatePaymentStatus'])->name('orders.payment_status');
    });
});
