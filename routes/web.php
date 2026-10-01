<?php

use App\Http\Controllers\Web\AuthController;
use App\Http\Controllers\Web\SiteController;
use Illuminate\Support\Facades\Route;

/*
 * The Blade frontend. Served by the same Laravel app as the API, so
 * `php artisan serve` runs the whole product on one address.
 */

Route::get('/',            [SiteController::class, 'home'])->name('home');
Route::get('/tools',       [SiteController::class, 'tools'])->name('tools');
Route::get('/tools/{slug}', [SiteController::class, 'workspace'])->name('workspace');
Route::get('/pricing',     [SiteController::class, 'pricing'])->name('pricing');
Route::get('/contact',     [SiteController::class, 'contact'])->name('contact');
Route::get('/p/{slug}',    [SiteController::class, 'page'])->name('page');
Route::get('/lang/{code}', [SiteController::class, 'setLanguage'])->name('lang');

Route::get('/login',     [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',    [AuthController::class, 'login']);
Route::get('/register',  [AuthController::class, 'showRegister'])->name('register');
Route::post('/register', [AuthController::class, 'register']);
Route::post('/logout',   [AuthController::class, 'logout'])->name('logout');
Route::get('/auth/google',           [AuthController::class, 'googleBridge']);
Route::post('/auth/google/exchange', [AuthController::class, 'googleExchange']);

// Custom package request (signed-in users) — mirrors POST /api/packages/request-custom
Route::post('/pricing/request-custom', [SiteController::class, 'requestCustom'])
    ->middleware('auth')->name('pricing.request-custom');

// ── Signed-in customer pages ──
Route::middleware('auth')->group(function () {
    Route::view('/account', 'account')->name('account');
});

// ── Admin panel (Blade) — session auth + admin role ──
Route::middleware(['auth', 'admin'])->prefix('admin')->group(function () {
    Route::view('/', 'admin.dashboard')->name('admin');
    Route::view('/customers', 'admin.customers');
    Route::view('/messages', 'admin.messages');
    Route::view('/shortcodes', 'admin.shortcodes');
    Route::view('/subscriptions', 'admin.subscriptions');
    Route::view('/usage', 'admin.usage');
    Route::view('/testimonials', 'admin.testimonials');
    Route::view('/taxonomies', 'admin.taxonomies');
    Route::view('/tools', 'admin.tools');
    Route::view('/packages', 'admin.packages');
    Route::view('/engines', 'admin.engines');
    Route::view('/keys', 'admin.keys');
    Route::view('/settings', 'admin.settings');
    Route::get('/tools/{id}', fn ($id) => view('admin.tool-editor', ['id' => (int) $id]))->whereNumber('id');
    Route::view('/pages', 'admin.pages');
    Route::view('/menu', 'admin.menu');
    Route::view('/appearance', 'admin.appearance');
    Route::view('/languages', 'admin.languages');

});

// ── Checkout + custom packages (session-authenticated, reusing the API logic) ──
Route::middleware('auth')->group(function () {
    Route::post('/checkout/stripe', [\App\Http\Controllers\Api\BillingController::class, 'checkout']);
    Route::post('/checkout/paypal', [\App\Http\Controllers\Api\BillingController::class, 'paypalCheckout']);
    Route::post('/pricing/request-custom', [\App\Http\Controllers\Api\BillingController::class, 'requestCustom']);
});
