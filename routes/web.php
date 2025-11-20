<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/cart', [CheckoutController::class, 'cart'])->name('cart');
Route::get('/cart/order_checkout', [CheckoutController::class, 'orderCheckout'])->name('checkout');
Route::get('/select', [CheckoutController::class, 'selectPayment'])->name('select-payment');
// Flexy routes with rate limiting
Route::get('/select/flexy', [CheckoutController::class, 'flexyForm'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('flexy-form');
Route::post('/select/flexy', [CheckoutController::class, 'submitFlexy'])
    ->middleware('throttle:3,1') // 3 requests per minute (prevent receipt spam)
    ->name('flexy-submit');
Route::get('/select/flexy/success', [CheckoutController::class, 'flexySuccess'])->name('flexy-success');
Route::get('/select/crypto/{encrypted_order_id}', [CheckoutController::class, 'cryptoForm'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('crypto-form');
Route::get('/crypto/{encrypted_order_id}', [CheckoutController::class, 'cryptoPayment'])->name('crypto-payment');
Route::get('/crypto/{encrypted_order_id}/success', [CheckoutController::class, 'cryptoPaymentSuccess'])->name('crypto-payment-success');

// API routes with rate limiting
Route::post('/api/packs', [CheckoutController::class, 'getPacks'])
    ->middleware('throttle:30,1') // 30 requests per minute
    ->name('api.packs');
Route::post('/api/orders/create', [CheckoutController::class, 'createOrder'])
    ->middleware('throttle:5,1') // 5 orders per minute (prevent spam)
    ->name('api.orders.create');
Route::post('/api/orders/get-by-encrypted-id', [CheckoutController::class, 'getOrderByEncryptedId'])
    ->middleware('throttle:10,1') // 10 requests per minute (prevent brute force)
    ->name('api.orders.get-by-encrypted-id');
Route::post('/api/orders/check-crypto-payment', [CheckoutController::class, 'checkCryptoPayment'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('api.orders.check-crypto-payment');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/signup', [AuthController::class, 'showSignupForm'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard routes (protected by auth middleware)
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/myaccount', [DashboardController::class, 'myAccount'])->name('dashboard.myaccount');
    Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/dashboard/invoices', [DashboardController::class, 'invoices'])->name('dashboard.invoices');
    Route::get('/dashboard/notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');
});

// Test route for Binance Pay credentials
Route::get('/test/binance', [CheckoutController::class, 'testBinanceCredentials'])->name('test.binance');

// Admin routes (protected by auth and admin middleware)
Route::prefix('adm')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});