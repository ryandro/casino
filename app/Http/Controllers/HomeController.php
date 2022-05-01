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
    public function TEMPgroupByProvider($slug)
    {
        if($slug === 'booongo') {
            $list = GameUtillityFunctions::retrieveGamesBooongo();
            //Log::notice($list);
        }

        return view('temp-gamelist-template')->with('gamesPagination', $list);
    }






    public function pagination($method, $amount = NULL, $extra_argument = NULL)
    {
        if($amount === NULL ) {
            $amount = 50;
        }



        try {
        // Add here dom if to use production, or even is able to leave bano00na, should be cached in main function in demo or to use spatie's pagination: https://spatie.be/docs/laravel-query-builder/v5/introduction

        if($method === 'index') {
            $gamelistCached = Gamelist::cachedGamelist();
            if($gamelistCached->count() > $amount) {
               $getGames = $gamelistCached->take($amount);
            } else {
                $getGames = $gamelistCached->take($gamelistCached->count());
            }

        } elseif($method === 'groupByProvider') {
            $gamelistCached = Gamelist::cachedGamelist()->where('provider', $extra_argument)->get();
            if($gamelistCached > $amount) {
               $getGames = $gamelistCached->take($amount);
            } else {
                $getGames = $gamelistCached->take($gamelistCached->count());
            }
        }

            return $getGames;


        // This should be stored in dom react/vue but i suck frontend hence for testing purposes of backend fraud instead of frontend all si bootstrapped

        $jsonObjectify = json_encode($getGames);
        } catch (Throwable $e) {
            
            if(env('APP_ENV') === 'local') {
                Log::debug('Gamelist retrieval error: '.$e);
            }

            return false;
        }

    }

}
