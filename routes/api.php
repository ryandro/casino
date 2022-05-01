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
// Don't forget to set this url also in .env or create seperated/dynamic config based on nginx reverse balancer (is very easy ones to find to setup 2-3 pointed, prolly needed in higher traffic as we'll be having much more calls in some games)

Route::get('/game_tunnel/in/{provider_slug} ', [App\Http\Controllers\GameTunnelAPI::class, 'in'])->name('callbackIn');
Route::get('/game_tunnel/out/{provider_slug} ', [App\Http\Controllers\GameTunnelAPI::class, 'out'])->name('callbackOut');

// Booongo Mixed
Route::any('/game_tunnel/mixed/booongo/{game_slug}/{device_type}/{token}/{mode}', [App\Http\Controllers\GameTunnelAPI::class, 'mixed'])->name('mixed');



// Need to add middleware group for internal VLAN requests preferably -- actually better so later can loadbalance the gamerouter in seperate instances
Route::get('/internal/gameRouter', [App\Http\Controllers\SlotmachineController::class, 'gameRouter'])->name('gameRouterInternal');

// Need to add external API middleware group for legit games to return callbacks or whatever, opens up to host the backend seperately also
Route::get('/external/gameRouter', [App\Http\Controllers\SlotmachineController::class, 'gameRouter'])->name('gameRouterExternal');

