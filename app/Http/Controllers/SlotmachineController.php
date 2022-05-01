<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Gamelist;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use \Carbon\Carbon;

class SlotmachineController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //$this->middleware('auth');
    }

    /**
     * Generate game for player
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function launcher(Request $request)
    {
        if(env('APP_ENV', 'local')) {
            Log::debug($request->fullUrl());
        }
        //Mode should be added (demo, currency etc.), currency should be in DOM of user


        $validateQueryData = $request->validate([
            'game_id' => ['required', 'max:35', 'min:3'],
            'provider' => ['optional', 'max:15'],
            'api_extension' => ['optional', 'max:10'],
        ]);

        if(!auth()->user()) {
            // ! Error user not logged in
        } else {
            /* User Retrieve & Balance */
            $playerID = auth()->user()->id;
            $balance = auth()->user()->balance('USD'); //can add multi currency
        }

        /* Game Select & Retrieve */
        $game_id = $request->game_id; 
        $selectGame = Gamelist::where('game_id', '=', $game_id)->first();

        if(!$selectGame) {
            // ! Error game_id not found 
        }

        $strLowerProvider = strtolower($selectGame->provider);
        if($request->provider) {
            $strLowerProvider = strtolower($request->provider);
        }
        $arrayAvailableProviders = 'bgaming, whatever'; // should be operator specific to check if provider is enabled for operator

        if(!isset($strLowerProvider, $arrayAvailableProviders)) {
            // ! Error provider not available or found
        }



        $buildArray = array(
            'game' => $selectGame->game_id,
            'provider' => $strLowerProvider,
            'player' => auth()->user()->id,
            'currency' => 'USD', // should be in request
            'mode' => 'real', //should be in request demo/real money play
            'method' => 'gameRequestByPlayer',
        );
        if(env('APP_ENV', 'local')) {
            Log::notice($buildArray);
        }


        $getGameUrl = Http::timeout(5)->get('http://localhost/api/internal/gameRouter', $buildArray);


        /* This is for same instance gamerouter passing, pretty much for staging:
        $jsonObject = json_encode($buildArray, true);
        $getGameUrl = self::gameRouter($jsonObject);
        */

        if(!$getGameUrl) {
            // ! Error retrieving game url
            return 'error retrieving game url'; 
        }

        return view('launcher')->with('content', $getGameUrl);
    }



    /**
     * Slotmachine router, possibly should be in a helper/config so there is more room to adapt & loadbalance on high volume
     * If having lot of provider integration this is handy to extend, as you want a normalized callback to operator easily
     * 
     */
    public function gameRouter(Request $request)
    {
        $fullContent = $request;
        $method = $fullContent->method;

        if($method === 'gameRequestByPlayer') {
            $provider = $fullContent->provider;

            // should add EXTRA options for example per game/id/game_type and most importantly per api_id, these should however have additional filters hence why need to be done by yourself

            if($provider === 'bgaming') {
                return self::bgamingSessionStart($request); 
            }
            if($provider === 'booongo') {
                return self::booongoSessionStart($request);
            }
            if($provider === 'playson') {
                return self::playsonSessionStart($request);
            }

            Log::critical('Provider method not found, this should not happen as at launcher() function, unless unsupported provider was tried to launch this should be checked.');
            return false;

        }
        
        return false;

    }

    /**
     *  Playson Sesssion Start (needs be refactored obvs)
     */
    public function playsonSessionStart(Request $request)
    {
        //Mapping to booongo (same API)
        return self::booongoSessionStart($request);
    }

    /**
     * Booongo Sesssion Start (needs be refactored obvs)
     */
    public function booongoSessionStart(Request $request)
    {
        // Will be trying diff method on this provisioning, in regards to 'balance modification' to hide within in there a simple socket/pusher //

        $fullContent = $request;
        $ourGameID = $fullContent->game;
        $selectGameBng = \App\Models\Gamelist::where('game_id', $ourGameID)->first(); // this shld be cached individually (on short cache game id strings)        
        $gameName = $selectGameBng->fullName;

        $api_origin_id = $selectGameBng->api_origin_id;
        //This case merged orig id (numeric) and orig game_hash/id together, so split these:
        $explodeIdMerge = explode('++', $api_origin_id);
        $orig_id = $explodeIdMerge[0];
        $orig_hash_id = $explodeIdMerge[1];

        $lang = "en";
        $timestamp = time();
        $compactSessionUrl = "https://gate-stage.betsrv.com/op/tigergames-stage/game.html?wl=demo&token=testtoken'.$timestamp.&game=".$orig_id."&lang=".$lang."&sound=1&ts=".$timestamp."&title=".$gameName."&platform=desktop";

        // Curling/loading in the session URL to server, ready to edit whatever to then display to user after //
        $ch = curl_init($compactSessionUrl);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        $html = curl_exec($ch);
        $redirectURL = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
        curl_close($ch);

        $launcherTest = Http::withOptions([
            'verify' => false,
        ])->get($redirectURL);



        $hardEditGameContent = str_replace('box7-stage.betsrv.com/gate-stage1/gs/', env('APP_BOOONGO_MIXED_API'), $launcherTest);
        //$hardEditGameContent = str_replace('appStarted = false', 'appStarted = true', $hardEditGameContent);
        $hardEditGameContent = str_replace('firstDetected = false', 'firstDetected = true', $hardEditGameContent);

        $finalLauncherContent = $hardEditGameContent;

        return view('launcher')->with('content', $finalLauncherContent);
    }




    /**
     * Bgaming (needs be refactored obvs)
     */
    public function bgamingSessionStart(Request $request)
    {

        $fullContent = $request;
        // In this test usecase  - I am using just demo method and adapt and change the demo currency & run auth/session on our own backend
        // Ofcourse, this can also be done with any currency, like korean WON or whatever shit native currency, while offering as USD 


        //real V
        $url = 'https://bgaming-network.com/play/'.$fullContent->game.'/FUN?server=demo';

        //testing V
        $url = 'https://bgaming-network.com/games/AztecMagicBonanza/FUN?play_token=08601e87-acde-432b-b58f-4380a82c1654';
        Log::notice($url);

        /* 

        < need ssl >

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT ,0); 
        curl_setopt($ch, CURLOPT_TIMEOUT, 60);
        $html = curl_exec($ch);
        $redirectURL = curl_getinfo($ch,CURLINFO_EFFECTIVE_URL );
        curl_close($ch);
        echo "Redirected URL: " . $redirectURL . "<br/>";
        return;


        */


        // Get game & session, apply this to user so also session can be reconnected towards, in case of BGAMING demo sessions will expire after 50-55 minutes, regardless if active or not, this means on full production.

        // You will need to bridge sessions (can use easy cron for this, as we are running in backend this won't need any appending or change in the frontend!)


        $curlingGame = Http::get($url);

        // str_replace basically is backend version of your regular js append/change
        $currency = $fullContent->currency;
        $mode = $fullContent->mode;


        if($mode === 'real') {
            $replaceCurrency = str_replace('FUN', $currency);
        }



        /** Replacing the API url on frontend to ours to connect as man in middle 
        // !! !! BELOW NEEDS TO GO TO README.MD !! !! //

        PLEASE NOTE: there is endless possibilities, you can for example have just a simple js to change the iframe src link to real session (to hide), the other way around by appending the source below instead.

        After the game iniatilized, the url can be changed back to bgaming, while it is still working on our api (after init connection)
        
        // AUTH //
        No need for sentry at all, no need for posting etc. etc. - frontend still is hosted completely by the provider, auto updated.

        The auth can be 1:1 based like provider, that means we simply put the session id to the player id, can use provider origin session, so you can easily check back games in back office @ provider.

        Auth in this case is based on laravel (<3) but ofc can be any, don't need any posts or whatever the fuck with sentry or seperating the auth at all - as any reputable provider will have their session system setup properly we just use their session id system.

        Example can be found at: www.cherry.games - where I changed origin bgaming slots in exact same fashion, but with a custom rebrandment
        
        **/

        // Check the API middleman function in this controller (to be made - 18:31pm)
        $replaceAPItoOurs = str_replace('https://bgaming-network.com/api/', env('APP_BGAMING_API'));

        // Need to get a VPS/server or open HTTP port for next steps so just putting this aside


        // Lets take the original token, we will use this in our view blade (and also not single frontend fucking sentry callback route on frontend is needed like u guys are doing)

        $finalGameContent = $replaceAPItoOurs; // not finished need open mocking API like said, above a small vps and/or opening my own ip, as for next step bgaming wil be sending us the slotmachine spins
        return $finalGameContent;

        return view('launcher')->with('content', $finalGameContent);

    }

}