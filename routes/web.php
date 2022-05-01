<?php

use Illuminate\Support\Facades\Route;

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
Route::get('/home', function () {
    return redirect('/');
});

Auth::routes();

Route::get('/', [App\Http\Controllers\HomeController::class, 'index'])->name('index');
Route::get('/provider/{provider_slug}', [App\Http\Controllers\HomeController::class, 'groupByProvider'])->name('groupByProvider');

// Need to add middleware for data group as is publically queried

Route::get('/data/gameslist', [App\Models\Gamelist::class, 'dataQueryGamelist'])->name('dataQueryGamelist');
Route::get('/data/gamesessions', [App\Models\GameSessions::class, 'dataQueryGameSessions'])->name('dataQueryGameSessions');

//Route::get('/data/providers', [App\Models\Gamelist::class, 'dataQueryProviders'])->name('dataQueryProviders');

//

Route::get('/launcher', [App\Http\Controllers\SlotmachineController::class, 'launcher'])->name('launcher');


