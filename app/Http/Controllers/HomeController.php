<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gamelist;

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


            // Add here dom if to use production, or even is able to leave bano00na, should be cached in main function in demo or to use spatie's pagination: https://spatie.be/docs/laravel-query-builder/v5/introduction
            if(Gamelist::count() > 9) {
               $getGames = Gamelist::all()->take('10');
            } else {
                $getGames = Gamelist::all()->take(Gamelist::count());
            }

            // This should be stored in dom react/vue but i suck frontend hence for testing purposes of backend fraud instead of frontend all si bootstrapped

            $jsonObjectify = json_encode($getGames);

            return view('index')->with('gamesPagination', $getGames);
        }

        return view('index');
    }
}
