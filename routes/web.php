<?php

use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Domain\POP\Http\Controllers\POPController;

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


// Placeholder rute katalog (akan kita isi nanti)
Route::prefix('catalog/print-on-paper')->name('pop.')->group(function(){
    Route::get('/', [POPController::class, 'index'])->name('index');
    Route::get('/{product:slug}', [POPController::class, 'show'])->name('show');
    Route::post('/quote', [POPController::class, 'quote'])->name('quote');
});

Route::prefix('catalog/mmt')->name('mmt.')->group(function () {
    Route::get('/', fn() => Inertia::render('Catalog/MMT/Index'))->name('index');
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
