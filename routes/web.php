<?php

use App\Http\Controllers\AiController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BootstrapController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\TemplateController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'create'])->name('login');
    Route::post('/login', [AuthController::class, 'store'])->middleware('throttle:10,1')->name('login.store');
    Route::get('/register/sponsor', [AuthController::class, 'sponsor'])->middleware('throttle:30,1')->name('register.sponsor');
    Route::get('/register', [AuthController::class, 'createRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'storeRegister'])->middleware('throttle:10,1')->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'createForgot'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'storeForgot'])->middleware('throttle:5,1')->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'createReset'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'storeReset'])->middleware('throttle:5,1')->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/{section}', [DashboardController::class, 'section'])
        ->where('section', 'ref-link|manage-profile|edit-profile|change-password|update-wallet-address|team-report|fund-management|income-report|withdrawal|support|document-list')
        ->name('dashboard.section');
    Route::get('/registered', [AuthController::class, 'registered'])->name('registered');
    Route::post('/logout', [AuthController::class, 'destroy'])->name('logout');
});

Route::get('/', [TemplateController::class, 'front'])->name('home');
Route::get('/templates', [TemplateController::class, 'index'])->name('templates.index');
Route::patch('/templates/{template}', [TemplateController::class, 'update'])->name('templates.update');
Route::get('/shop', [ShopController::class, 'index'])->name('shop');
Route::get('/products/{product}', [ShopController::class, 'show'])->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::post('/cart/promo', [CartController::class, 'promo'])->name('cart.promo');
Route::post('/cart/shipping', [CartController::class, 'shipping'])->name('cart.shipping');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->name('checkout.store');

Route::get('/ai', [AiController::class, 'home'])->name('ai.home');
Route::get('/ai/product', [AiController::class, 'product'])->name('ai.product');
Route::get('/ai/pricing', [AiController::class, 'pricing'])->name('ai.pricing');
Route::get('/ai/workspace', [AiController::class, 'workspace'])->name('ai.workspace');

Route::get('/bootstrap', [BootstrapController::class, 'home'])->name('bootstrap.home');
Route::get('/bootstrap/services', [BootstrapController::class, 'services'])->name('bootstrap.services');
Route::get('/bootstrap/contact', [BootstrapController::class, 'createContact'])->name('bootstrap.contact');
Route::post('/bootstrap/contact', [BootstrapController::class, 'storeContact'])->middleware('throttle:10,1')->name('bootstrap.contact.store');

Route::post('/newsletter', function (Request $request) {
    $request->validate(['email' => ['required', 'email']]);

    return back()->with('status', 'You are on the list. This demo does not send email.');
})->name('newsletter.store');
