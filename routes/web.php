<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/mobilelegends', [HomeController::class, 'mobileLegends'])->name('mobilelegends');
Route::get('/free-fire-diamonds-top-up', function() {
    $controller = app(HomeController::class);
    return $controller->gameTopUp('freefire');
})->name('freefire');
Route::get('/pubg-mobile-uc-top-up-global', function() {
    $controller = app(HomeController::class);
    return $controller->gameTopUp('pubgmobile');
})->name('pubgmobile');
Route::get('/honor-of-kings-tokens-top-up-global', function() {
    $controller = app(HomeController::class);
    return $controller->gameTopUp('honorofkings');
})->name('honorofkings');
Route::get('/blood-strike-golds-top-up-global', function() {
    $controller = app(HomeController::class);
    return $controller->gameTopUp('bloodstrike');
})->name('bloodstrike');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/terms-of-use', [HomeController::class, 'termsOfUse'])->name('terms-of-use');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
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
Route::get('/select/bmccp/{encrypted_order_id}', [CheckoutController::class, 'baridimobForm'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('baridimob-form');
Route::post('/api/baridimob/process', [CheckoutController::class, 'processBaridimobPayment'])
    ->middleware('throttle:5,1') // 5 requests per minute
    ->name('api.baridimob.process');
Route::post('/webhook/baridimob', [CheckoutController::class, 'baridimobWebhook'])
    ->name('baridimob.webhook');
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
Route::post('/api/validate-nickname', [CheckoutController::class, 'validateNickname'])
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('api.validate-nickname');
Route::post('/api/orders/check-crypto-payment', [CheckoutController::class, 'checkCryptoPayment'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('api.orders.check-crypto-payment');
Route::post('/api/orders/delete', [CheckoutController::class, 'deleteOrder'])
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('api.orders.delete');

// Authentication routes
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::get('/signup', [AuthController::class, 'showSignupForm'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Dashboard routes - orders, invoices, notifications accessible without auth
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
Route::get('/dashboard/invoices', [DashboardController::class, 'invoices'])->name('dashboard.invoices');
Route::get('/dashboard/notifications', [DashboardController::class, 'notifications'])->name('dashboard.notifications');

// Dashboard routes (protected by auth middleware) - only myaccount requires auth
Route::middleware('auth')->group(function () {
    Route::get('/dashboard/myaccount', [DashboardController::class, 'myAccount'])->name('dashboard.myaccount');
});

// Test routes for NOWPayments
Route::get('/test/nowpayments', [CheckoutController::class, 'testNowPaymentsCredentials'])->name('test.nowpayments');
Route::get('/test/chargily', [CheckoutController::class, 'testChargilyCredentials'])->name('test.chargily');
Route::get('/test/nowpayments/payment', [CheckoutController::class, 'testNowPaymentsPayment'])->name('test.nowpayments.payment');
Route::get('/test/nowpayments/status/{payment_id}', [CheckoutController::class, 'testNowPaymentsStatus'])->name('test.nowpayments.status');

// NOWPayments webhook route (no CSRF protection needed for webhooks)
Route::post('/webhook/nowpayments', [CheckoutController::class, 'nowPaymentsWebhook'])->name('nowpayments.webhook');

// MixPay webhook route (no CSRF protection needed for webhooks)
Route::post('/webhook/mixpay', [CheckoutController::class, 'mixPayWebhook'])->name('mixpay.webhook');

// GET route for webhook testing/info (not used by NOWPayments, but helpful for debugging)
Route::get('/webhook/nowpayments', function() {
    return response()->json([
        'message' => 'NOWPayments webhook endpoint',
        'note' => 'This endpoint only accepts POST requests from NOWPayments servers',
        'webhook_url' => route('nowpayments.webhook'),
        'test_credentials' => route('test.nowpayments'),
    ], 200);
});

// Admin routes (protected by auth and admin middleware)
Route::prefix('adm')->name('admin.')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');
});