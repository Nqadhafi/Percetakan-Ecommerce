<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Domain\POP\Http\Controllers\POPController;
use App\Domain\MMT\Http\Controllers\MMTController;
use App\Commerce\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Profile\ProfileController;
use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Customer\CustomerOrderController;
use App\Domain\Sticker\Http\Controllers\StickerController;
use App\Domain\Merch\Http\Controllers\MerchController;
use App\Domain\BusinessPrint\Http\Controllers\BusinessPrintController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminMerchProductController;
use App\Http\Controllers\Admin\AdminBizProductController;
use App\Http\Controllers\Customer\DashboardController as CustomerDashboard;


/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return Inertia::render('Front/Home');
})->name('front.home');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile.index');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    
    Route::get('/checkout/success/{code}', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/orders', [CustomerOrderController::class, 'index'])->name('orders.index');
    Route::get('/orders/{code}', [CustomerOrderController::class, 'show'])->name('orders.show');
    Route::prefix('cart')->name('cart.')->group(function(){
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update/{item}', [CartController::class, 'update'])->name('update');
    Route::post('/remove/{item}', [CartController::class, 'remove'])->name('remove');

});
});
Route::middleware(['auth','cart.not_empty'])->group(function(){
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/submit', [CheckoutController::class, 'submit'])->name('checkout.submit');
    
});

// Placeholder rute katalog (akan kita isi nanti)
Route::prefix('catalog/print-on-paper')->name('pop.')->group(function(){
    Route::get('/', [POPController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [POPController::class, 'show'])->name('show');
    Route::post('/quote', [POPController::class, 'quote'])->name('quote');
});

Route::prefix('catalog/mmt')->name('mmt.')->group(function(){
    Route::get('/', [MMTController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [MMTController::class, 'show'])->name('show');
    Route::post('/quote', [MMTController::class, 'quote'])->name('quote');
});

Route::prefix('catalog/sticker')->name('sticker.')->group(function(){
    Route::get('/', [StickerController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [StickerController::class, 'show'])->name('show');
    Route::post('/quote', [StickerController::class, 'quote'])->name('quote');
});

Route::prefix('catalog/business-print')->name('biz.')->group(function(){
    Route::get('/', [BusinessPrintController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [BusinessPrintController::class, 'show'])->name('show');
    Route::post('/quote', [BusinessPrintController::class, 'quote'])->name('quote');
});
Route::prefix('catalog/merch')->name('merch.')->group(function(){
    Route::get('/', [ MerchController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [MerchController::class, 'show'])->name('show');
    Route::post('/quote', [MerchController::class, 'quote'])->name('quote');
});

Route::middleware(['auth', 'is_staff'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // dashboard ringkas nanti bisa ditambah /admin
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{code}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::patch('/orders/{code}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.updateStatus');
    });

    Route::middleware(['auth','is_admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        // List semua produk merch
        Route::get('/merch-products', [AdminMerchProductController::class, 'index'])
            ->name('merch.index');
        Route::get('/merch-products/create', [AdminMerchProductController::class, 'create'])
            ->name('merch.create');
        Route::post('/merch-products', [AdminMerchProductController::class, 'store'])
            ->name('merch.store');
        Route::get('/merch-products/{id}/edit', [AdminMerchProductController::class, 'edit'])
            ->name('merch.edit');
        Route::patch('/merch-products/{id}', [AdminMerchProductController::class, 'updateProduct'])
            ->name('merch.updateProduct');
        Route::post('/merch-products/{id}/variants', [AdminMerchProductController::class, 'storeVariant'])
            ->name('merch.variant.store');
        Route::patch('/merch-variants/{variantId}', [AdminMerchProductController::class, 'updateVariant'])
            ->name('merch.variant.update');
        Route::post('/merch-variants/{variantId}/tiers', [AdminMerchProductController::class, 'storeTier'])
            ->name('merch.tier.store');
        Route::patch('/merch-tiers/{tierId}', [AdminMerchProductController::class, 'updateTier'])
            ->name('merch.tier.update');

        
        // Daftar produk cetak bisnis
// Business Print
        Route::get('/biz-products', [AdminBizProductController::class, 'index'])
            ->name('biz.index');
        Route::get('/biz-products/create', [AdminBizProductController::class, 'create'])
            ->name('biz.create');
        Route::post('/biz-products', [AdminBizProductController::class, 'store'])
            ->name('biz.store');
        Route::get('/biz-products/{id}/edit', [AdminBizProductController::class, 'edit'])
            ->name('biz.edit');
        Route::patch('/biz-products/{id}', [AdminBizProductController::class, 'updateProduct'])
            ->name('biz.updateProduct');

        // addon endpoints
        Route::post('/biz-products/{id}/addons', [AdminBizProductController::class, 'storeAddon'])
            ->name('biz.addon.store');
        Route::patch('/biz-addons/{addonId}', [AdminBizProductController::class, 'updateAddon'])
            ->name('biz.addon.update');
        Route::patch('/biz-addons/{addonId}/disable', [AdminBizProductController::class, 'disableAddon'])
            ->name('biz.addon.disable');
            });

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
 Route::get('/dashboard', [CustomerDashboard::class, 'index'])
        ->name('customer.dashboard');
});
