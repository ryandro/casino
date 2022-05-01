<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gamelist;
use App\Http\Controllers\GameUtillityFunctions;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;



class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        if(auth()->user()) {
            return view('index')->with('gamesPagination', self::pagination('index'));
        }

        return view('index');
    }


    /**
     * TEMP- retrieve listing/utillity for provider
     */
    public function TEMPgroupByProvider(Request $request)
    {

      
        return view('temp-gamelist-template')->with('gamesPagination', self::pagination('groupByProvider', '50', $slug));
    }



    public function pagination($method, $amount = NULL, $extra_argument = NULL)
    {

        /* TO DO:
        return Validator::make($data, [
            'slug' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);


        added laravel spatie, which should be used prolly standalone from models

        */

        if($amount === NULL ) {
            $amount = 50;
        }

        try {
            if($method === 'index') {
                $gamelistCached = Gamelist::cachedGamelist();
                if($gamelistCached->count() > $amount) {
                   $getGames = $gamelistCached->take($amount);
                } else {
                    $getGames = $gamelistCached->take($gamelistCached->count());
                }

            } elseif($method === 'groupByProvider') {
                $gamelistCached = Gamelist::cachedGamelist()->where('provider', $extra_argument);
                if($gamelistCached > $amount) {
                   $getGames = $gamelistCached->take($amount);
                } else {
                    $getGames = $gamelistCached->take($gamelistCached->count());
                }
            }

        return $getGames;

        } catch (Throwable $e) {
            if(env('APP_ENV') === 'local') {
                Log::debug('Gamelist retrieval error: '.$e);
            }
            return false;
        }

    }

}
