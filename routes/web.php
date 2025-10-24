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




Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
 Route::get('/dashboard', [CustomerDashboard::class, 'index'])
        ->name('customer.dashboard');
});
