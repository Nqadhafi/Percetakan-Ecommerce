<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Domain\POP\Http\Controllers\POPController;
use App\Domain\MMT\Http\Controllers\MMTController;
use App\Commerce\Cart\Http\Controllers\CartController;
use App\Http\Controllers\Profile\ProfileController;

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



Route::prefix('cart')->name('cart.')->group(function(){
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/update/{item}', [CartController::class, 'update'])->name('update');
    Route::post('/remove/{item}', [CartController::class, 'remove'])->name('remove');
});

Route::middleware([
    'auth:sanctum',
    config('jetstream.auth_session'),
    'verified',
])->group(function () {
    Route::get('/dashboard', function () {
        return Inertia::render('Dashboard');
    })->name('dashboard');
});
