<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/


Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});


// Need to add middleware group for internal VLAN requests preferably -- actually better so later can loadbalance the gamerouter in seperate instances
Route::get('/internal/gameRouter', [App\Http\Controllers\SlotmachineController::class, 'gameRouter'])->name('gameRouterInternal');

// Need to add external API middleware group for legit games to return callbacks or whatever, opens up to host the backend seperately also
Route::get('/external/gameRouter', [App\Http\Controllers\SlotmachineController::class, 'gameRouter'])->name('gameRouterExternal');

