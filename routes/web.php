<?php

use App\Http\Controllers\Admin\SellerManagementController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CouponController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Seller\SellerAuthController;
use App\Http\Controllers\Seller\SellerController;
use App\Http\Controllers\Seller\SellerStorefrontController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Session;

Route::get('/', [HomeController::class, 'index'])->name('home');

// Test translation debug route
Route::get('/test/translation', function () {
    $loader = app('translation.loader');
    $langPath = app('path.lang');
    $locale = app()->getLocale();

    // Force reload translations
    app('translator')->setLoaded([]);

    return response()->json([
        'locale' => $locale,
        'lang_path' => $langPath,
        'lang_path_resolved' => base_path('lang'),
        'nav_file_exists' => file_exists($langPath.'/en/nav.php'),
        'home_file_exists' => file_exists($langPath.'/'.$locale.'/home.php'),
        'home_file_path' => $langPath.'/'.$locale.'/home.php',
        'home_file_content' => file_exists($langPath.'/'.$locale.'/home.php') ? require ($langPath.'/'.$locale.'/home.php') : null,
        'loader_result' => $loader->load($locale, 'home', '*'),
        'loader_class' => get_class($loader),
        'translation_test' => __('nav.home'),
        'lightning_fast_test' => __('home.lightning_fast'),
        'lightning_fast_raw' => trans('home.lightning_fast', [], $locale),
        'translator_loaded' => app('translator')->getLoader(),
    ]);
});

// Language switching route
Route::get('/language/{locale}', function ($locale) {
    $availableLocales = ['en', 'fr', 'ar'];
    if (in_array($locale, $availableLocales)) {
        Session::put('locale', $locale);
    }

    return redirect()->back();
})->name('language.switch');

Route::get('/mobilelegends', [HomeController::class, 'mobileLegends'])->name('mobilelegends');
Route::get('/free-fire-diamonds-top-up', function () {
    $controller = app(HomeController::class);

    return $controller->gameTopUp('freefire');
})->name('freefire');
Route::get('/pubg-mobile-uc-top-up-global', function () {
    $controller = app(HomeController::class);

    return $controller->gameTopUp('pubgmobile');
})->name('pubgmobile');
Route::get('/honor-of-kings-tokens-top-up-global', function () {
    $controller = app(HomeController::class);

    return $controller->gameTopUp('honorofkings');
})->name('honorofkings');
Route::get('/blood-strike-golds-top-up-global', function () {
    $controller = app(HomeController::class);

    return $controller->gameTopUp('bloodstrike');
})->name('bloodstrike');
Route::get('/steam-gift-cards', function () {
    $controller = app(HomeController::class);

    return $controller->gameTopUp('steam_giftcard');
})->name('steam_giftcard');
Route::get('/shop', [HomeController::class, 'shop'])->name('shop');
Route::get('/api/search', [HomeController::class, 'searchAjax'])->name('api.search');
Route::get('/about', [HomeController::class, 'about'])->name('about');
Route::get('/terms-of-use', [HomeController::class, 'termsOfUse'])->name('terms-of-use');
Route::get('/privacy-policy', [HomeController::class, 'privacyPolicy'])->name('privacy-policy');
Route::get('/contact', [HomeController::class, 'contact'])->name('contact');
Route::post('/contact', [HomeController::class, 'contactSubmit'])->name('contact.submit');
Route::post('/api/games/review', [HomeController::class, 'submitReview'])->name('api.games.review');
Route::get('/api/games/{gameId}/reviews', [HomeController::class, 'getGameReviews'])->name('api.games.reviews');
Route::post('/api/reviews/{reviewId}/like', [HomeController::class, 'toggleReviewLike'])->name('api.reviews.like');
Route::post('/api/reviews/{reviewId}/reply', [HomeController::class, 'submitReviewReply'])->name('api.reviews.reply');
Route::get('/api/reviews/{reviewId}/replies', [HomeController::class, 'getReviewReplies'])->name('api.reviews.replies');
Route::get('/cart', [CheckoutController::class, 'cart'])->name('cart');
Route::post('/api/cart/validate', [CheckoutController::class, 'validateCartItems'])->name('api.cart.validate');
Route::get('/cart/order_checkout', [CheckoutController::class, 'orderCheckout'])->name('checkout');
Route::get('/select', [CheckoutController::class, 'selectPayment'])->name('select-payment');
Route::post('/api/cart/convert-to-usd', [CheckoutController::class, 'convertCartToUsd'])->name('api.cart.convert-to-usd');
// Flexy routes with rate limiting
Route::get('/select/flexy', [CheckoutController::class, 'flexyForm'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('flexy-form');
Route::post('/select/flexy', [CheckoutController::class, 'submitFlexy'])
    ->name('flexy-submit');
Route::get('/select/flexy/success', [CheckoutController::class, 'flexySuccess'])->name('flexy-success');
Route::get('/select/crypto/{encrypted_order_id}', [CheckoutController::class, 'cryptoForm'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('crypto-form');
Route::get('/select/bmccp/{encrypted_order_id}', [CheckoutController::class, 'baridimobForm'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('baridimob-form');
Route::get('/payment/success/{encrypted_order_id}', [CheckoutController::class, 'paymentSuccess'])
    ->name('payment.success');
Route::get('/payment/sofizpay/cib/return', [CheckoutController::class, 'sofizpayCibReturn'])
    ->middleware('throttle:30,1')
    ->name('payment.sofizpay.cib.return');
Route::post('/api/baridimob/process', [CheckoutController::class, 'processBaridimobPayment'])
    ->name('api.baridimob.process');
Route::post('/webhook/baridimob', [CheckoutController::class, 'baridimobWebhook'])
    ->name('baridimob.webhook');
Route::post('/webhook/vipreseller', [AdminController::class, 'vipResellerWebhook'])
    ->name('vipreseller.webhook');
Route::post('/webhook/digiflazz', [\App\Http\Controllers\Webhook\DigiflazzWebhookController::class, 'handle'])
    // Remove the 'web' group and explicit CSRF middleware so external POSTs are not blocked
    ->withoutMiddleware(['web', \App\Http\Middleware\VerifyCsrfToken::class])
    ->middleware([\App\Http\Middleware\VerifyDigiflazzSignature::class, 'throttle:60,1'])
    ->name('digiflazz.webhook');

// Order status API for customers
Route::get('/api/orders/{order}/status', [\App\Http\Controllers\CheckoutController::class, 'getOrderStatus'])
    ->middleware('auth')
    ->name('api.orders.status');

// Order status API for sellers (seller guard)
Route::get('/seller/api/orders/{order}/status', [\App\Http\Controllers\Seller\SellerController::class, 'getOrderStatusForSeller'])
    ->middleware('seller')
    ->name('seller.api.orders.status');
Route::post('/webhook/telegram', [AdminController::class, 'telegramWebhook'])
    ->name('telegram.webhook');
Route::get('/crypto/{encrypted_order_id}', [CheckoutController::class, 'cryptoPayment'])->name('crypto-payment');
Route::get('/crypto/{encrypted_order_id}/success', [CheckoutController::class, 'cryptoPaymentSuccess'])->name('crypto-payment-success');

// Order success page for free coupon orders
Route::get('/order/success/{order}', [CheckoutController::class, 'orderSuccess'])
    ->middleware('auth')
    ->name('order.success');

// API routes with rate limiting
Route::post('/api/packs', [CheckoutController::class, 'getPacks'])
    ->middleware('throttle:30,1') // 30 requests per minute
    ->name('api.packs');
Route::post('/api/orders/create', [CheckoutController::class, 'createOrder'])
    ->middleware(['auth', 'throttle:20,1']) // Logged-in customers only; 20 orders per minute per IP
    ->name('api.orders.create');
Route::post('/api/orders/get-by-encrypted-id', [CheckoutController::class, 'getOrderByEncryptedId'])
    ->middleware('throttle:10,1') // 10 requests per minute (prevent brute force)
    ->name('api.orders.get-by-encrypted-id');
Route::get('/api/orders/mine', [CheckoutController::class, 'listMyOrders'])
    ->middleware(['auth', 'throttle:60,1'])
    ->name('api.orders.mine');
Route::post('/api/validate-nickname', [CheckoutController::class, 'validateNickname'])
    ->middleware('throttle:30,1')
    ->name('api.validate-nickname');
Route::post('/api/orders/check-crypto-payment', [CheckoutController::class, 'checkCryptoPayment'])
    ->middleware('throttle:20,1') // 20 requests per minute
    ->name('api.orders.check-crypto-payment');
Route::post('/api/orders/delete', [CheckoutController::class, 'deleteOrder'])
    ->middleware('throttle:10,1') // 10 requests per minute
    ->name('api.orders.delete');

// Coupon API routes (auth checked in controller, not middleware)
Route::post('/api/coupon/validate', [CouponController::class, 'validate'])
    ->name('api.coupon.validate');
Route::post('/api/coupon/remove', [CouponController::class, 'remove'])
    ->name('api.coupon.remove');
Route::post('/api/coupon/process-free-order', [CouponController::class, 'processFreeOrder'])
    ->middleware('auth') // Only free order requires auth middleware
    ->name('api.coupon.process-free-order');

// Authentication routes (Google OAuth only for customers)
Route::get('/auth/google', [AuthController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [AuthController::class, 'handleGoogleCallback'])->name('auth.google.callback');
Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:10,1');
Route::get('/signup', [AuthController::class, 'showSignupForm'])->name('signup');
Route::post('/signup', [AuthController::class, 'signup'])->middleware('throttle:10,1');
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
Route::get('/webhook/nowpayments', function () {
    return response()->json([
        'message' => 'NOWPayments webhook endpoint',
        'note' => 'This endpoint only accepts POST requests from NOWPayments servers',
        'webhook_url' => route('nowpayments.webhook'),
        'test_credentials' => route('test.nowpayments'),
    ], 200);
});

// Admin routes (protected by auth and admin middleware + rate limiting)
Route::prefix('adm')->name('admin.')->middleware(['auth', 'admin', 'throttle:60,1'])->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::patch('/users/{id}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
    Route::get('/orders', [AdminController::class, 'orders'])->name('orders');
    Route::get('/orders/data', [AdminController::class, 'orders'])->name('orders.data'); // DataTables endpoint
    Route::get('/orders/{orderNumber}', [AdminController::class, 'getOrderDetails'])->name('orders.details');
    Route::patch('/orders/{orderNumber}/status', [AdminController::class, 'updateOrderStatus'])->name('orders.update-status');
    // Flexy approval flows
    Route::get('/flexy-approvals', [AdminController::class, 'flexyApprovals'])->name('flexy.approvals');
    Route::patch('/flexy-approvals/{orderNumber}/approve', [AdminController::class, 'approveFlexy'])->name('flexy.approvals.approve');
    Route::patch('/flexy-approvals/{orderNumber}/reject', [AdminController::class, 'rejectFlexy'])->name('flexy.approvals.reject');
    // Payouts removed - platform no longer supports seller payouts
    // Top-up requests management
    Route::get('/topups', [\App\Http\Controllers\Admin\TopupController::class, 'index'])->name('topups.index');
    Route::patch('/topups/{topup}/approve', [\App\Http\Controllers\Admin\TopupController::class, 'approve'])->name('topups.approve');
    Route::patch('/topups/{topup}/reject', [\App\Http\Controllers\Admin\TopupController::class, 'reject'])->name('topups.reject');
    Route::get('/settings', [AdminController::class, 'settings'])->name('settings');

    // Game Content Management Routes
    Route::prefix('game-content')->name('game-content.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\GameContentController::class, 'index'])->name('index');
        Route::get('/{game}/edit', [\App\Http\Controllers\Admin\GameContentController::class, 'edit'])->name('edit');
        Route::post('/{game}/store', [\App\Http\Controllers\Admin\GameContentController::class, 'store'])->name('store');
        Route::post('/{game}/images', [\App\Http\Controllers\Admin\GameContentController::class, 'storeImage'])->name('images.store');
        Route::delete('/{game}/images/{image}', [\App\Http\Controllers\Admin\GameContentController::class, 'deleteImage'])->name('images.delete');
        Route::patch('/{game}/images/order', [\App\Http\Controllers\Admin\GameContentController::class, 'updateImageOrder'])->name('images.order');
    });

    // Seller Management Routes
    Route::prefix('sellers')->name('sellers.')->group(function () {
        Route::get('/', [SellerManagementController::class, 'index'])->name('index');
        Route::get('/create', [SellerManagementController::class, 'create'])->name('create');
        Route::post('/', [SellerManagementController::class, 'store'])->name('store');
        Route::get('/{seller}', [SellerManagementController::class, 'show'])->name('show');
        Route::get('/{seller}/edit', [SellerManagementController::class, 'edit'])->name('edit');
        Route::put('/{seller}', [SellerManagementController::class, 'update'])->name('update');
        Route::delete('/{seller}', [SellerManagementController::class, 'destroy'])->name('destroy');
        Route::post('/{seller}/topup', [SellerManagementController::class, 'topupWallet'])->name('topup');
        Route::post('/{seller}/deduct', [SellerManagementController::class, 'deductWallet'])->name('deduct');
        Route::patch('/{seller}/status', [SellerManagementController::class, 'updateStatus'])->name('status');
        Route::get('/{seller}/pricing', [SellerManagementController::class, 'pricing'])->name('pricing');
        Route::get('/{seller}/orders', [SellerManagementController::class, 'orders'])->name('orders');
        Route::get('/{seller}/transactions', [SellerManagementController::class, 'transactions'])->name('transactions');
    });
});

// =====================================================
// SELLER ROUTES
// =====================================================

// Seller Auth Routes (Guest sellers only)
Route::prefix('seller')->name('seller.')->group(function () {
    Route::get('/login', [SellerAuthController::class, 'showLoginForm'])->name('login');
    // Protect login POST with rate limit to prevent brute force
    Route::post('/login', [SellerAuthController::class, 'login'])->middleware('throttle:5,1')->name('login.submit');
    Route::get('/register', [SellerAuthController::class, 'showRegisterForm'])->name('register');
    // Protect registration with rate limit to prevent abuse
    Route::post('/register', [SellerAuthController::class, 'register'])->middleware('throttle:3,1')->name('register.submit');
    Route::post('/logout', [SellerAuthController::class, 'logout'])->name('logout');
});

// Seller Dashboard Routes (Protected by seller middleware)
Route::prefix('seller')->name('seller.')->middleware('seller')->group(function () {
    Route::get('/dashboard', [SellerController::class, 'dashboard'])->name('dashboard');
    Route::get('/packs', [SellerController::class, 'packs'])->name('packs');
    Route::post('/packs/update-prices', [SellerController::class, 'updatePrices'])->name('packs.update-prices');
    Route::get('/wallet', [SellerController::class, 'wallet'])->name('wallet');
    // Seller payouts removed - leave route out
    // Seller-initiated top-up requests (admin approval required to credit wallet)
    Route::post('/topup/request', [App\Http\Controllers\Seller\SellerTopupController::class, 'store'])->name('topup.request');
    Route::get('/orders', [SellerController::class, 'orders'])->name('orders');
    Route::get('/statistics', [SellerController::class, 'statistics'])->name('statistics');
    Route::get('/direct-topup', [SellerController::class, 'directTopup'])->name('direct-topup');
    Route::post('/direct-topup', [SellerController::class, 'processDirectTopup'])->name('direct-topup.process');
    Route::get('/profile', [SellerController::class, 'profile'])->name('profile');
    Route::get('/settings', [SellerController::class, 'settings'])->name('settings');
    Route::post('/settings', [SellerController::class, 'updateSettings'])->name('settings.update');
    Route::post('/settings/remove-image', [SellerController::class, 'removeImage'])->name('settings.remove-image');
    // AJAX endpoint to check whether a desired store slug / username is available
    Route::post('/settings/check-slug', [SellerController::class, 'checkSlugAvailability'])->name('settings.check-slug');
    Route::put('/profile', [SellerController::class, 'updateProfile'])->name('profile.update');
    Route::post('/profile/password', [SellerController::class, 'changePassword'])->name('profile.password');

    // Order Management API
    Route::get('/orders/{orderNumber}', [SellerController::class, 'getOrderDetails'])->name('orders.details');
    Route::patch('/orders/{orderNumber}/confirm', [SellerController::class, 'confirmFlexyOrder'])->name('orders.confirm');
    Route::delete('/orders/{orderNumber}', [SellerController::class, 'deleteOrder'])->name('orders.delete');

    // API for seller dashboard
    Route::get('/api/packs', [SellerController::class, 'getGamePacks'])->name('api.packs');
});

// Seller Storefront Public Routes
Route::get('/store/{username}', [SellerStorefrontController::class, 'home'])->name('seller.store.home');
// Specific storefront routes (must be placed before the generic game page to avoid collisions)
Route::get('/store/{username}/payment-method', [SellerStorefrontController::class, 'showPaymentMethod'])->name('seller.store.payment-method');
Route::post('/store/{username}/payment', [SellerStorefrontController::class, 'processPayment'])->name('seller.store.payment');
Route::post('/store/{username}/checkout', [SellerStorefrontController::class, 'checkout'])->name('seller.store.checkout');
Route::get('/store/payment/success/{encrypted_order_id}', [SellerStorefrontController::class, 'paymentSuccess'])->name('seller.payment.success');
// API endpoint: get flexy-specific seller price for a pack (used by storefront JS)
Route::get('/store/{username}/pack/{pack}/flexy-price', [SellerStorefrontController::class, 'getFlexyPrice'])->name('seller.store.flexy-price');
// Generic game route should come last to avoid matching specific paths like 'payment-method' or 'pack'
Route::get('/store/{username}/{gameType}', [SellerStorefrontController::class, 'gamePage'])->name('seller.store.game');
Route::get('/store/{username}/{gameType}/packs', [SellerStorefrontController::class, 'getPacksApi'])->name('seller.store.packs');
Route::get('/store/payment/success/{encrypted_order_id}', [SellerStorefrontController::class, 'paymentSuccess'])->name('seller.payment.success');

// Dynamic game routes - matches snake_case game names (e.g., /arena_breakout, /naruto_shippuden)
// Must be placed last to avoid conflicts with other routes
Route::get('/{gameType}', function ($gameType) {
    // Only allow snake_case game types (letters, numbers, underscores)
    if (! preg_match('/^[a-z0-9_]+$/', $gameType)) {
        abort(404);
    }

    // Normalize Genshin Impact variants to 'genshin_impact'
    // If the URL is a genshin_impact variant (e.g., genshin_impact_genesis_crystals),
    // normalize it to genshin_impact for consistent routing
    $normalizedGameType = $gameType;
    if (strpos($gameType, 'genshin_impact') === 0 && $gameType !== 'genshin_impact') {
        // Redirect genshin_impact variants to the base /genshin_impact route
        return redirect('/genshin_impact', 301);
    }

    // Check if game type has packs in database
    $hasPacks = \App\Models\DiamondPack::where('game_type', $gameType)->exists();
    if (! $hasPacks) {
        abort(404);
    }

    $controller = app(\App\Http\Controllers\HomeController::class);

    return $controller->gameTopUp($gameType);
})->where('gameType', '[a-z0-9_]+')->name('game-topup');
