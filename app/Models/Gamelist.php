<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use \Carbon\Carbon; 
use Spatie\QueryBuilder\QueryBuilder;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;

class Gamelist extends Model
{
    protected $table = 'gamelist';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'fullName',
        'provider',
        'thumbnail',
        'isHot',
        'isNew',
        'tags',
        'open',
        'rtpDes',
        'category',
        'order_rating',
        'thumbnail_ext',
        'short_desc', 
        'funplay'
    ];

    protected $hidden = [
        'open',
        'api_extension',
        'api_origin_id',
        'api_extra'
    ];

    public static function dataQueryGamelist() {
        $data = QueryBuilder::for(Gamelist::class)->allowedFields(['game_id', 'fullName', 'funplay', 'open', 'thumbnail', 'isHot', 'isNew'])->allowedSorts('provider')->paginate()->appends(request()->query());

        return $data;
    }

    public static function buildGamesList($method = NULL) {

        $getAllGames = self::all();
    
            $index = 0;
            foreach($getAllGames as $game) {

            $provider = $game['provider'];
            $presetCompactID = 'provider_'.$game['provider'];
            $getPreset = \App\Models\Presets::returnBoolValue($presetCompactID);

            if($getPreset) {
            $index++;
                $array[] = $game;
            }

        }
        if($method === 'count') {
            return $index;
        }

        return $array;

    }


    public static function cachedGamelist($method = NULL) {

        $gamelistResponse = Cache::get('cachedGamelist');

        if(env('APP_ENV' === 'local')) {
                Artisan::command('optimize:clear'); 
        }

        if(!$gamelistResponse) {
            $gamelistResponse = self::buildGamesList();

            $gamelist = Cache::put('cachedGamelist', $gamelistResponse, config('app.cache_gamelist_length')); // in minutes cache
            $gameCount = Cache::put('cachedGamelistCount', self::buildGamesList('count'), config('app.cache_gamelist_length')); // in minutes cache
        }


        if($method === 'count') {
            return Cache::get('cachedGamelistCount') ?? 0;
        }

        return $gamelistResponse;
    }

    public static function cachedIndividualGame($game_id) {
        $gamespecificCached = Cache::get('cachedIndividualGame'.$game_id);

        if(env('APP_ENV' === 'local')) { Artisan::command('optimize:clear'); }

        if(!$gamespecificCached) {
            $selectGame = self::cachedGamelist();
            $selectGame = Arr::get($selectGame, 'game_id.'.$game_id);

            if($selectGame) {
            $gamespecificCached = Cache::put('cachedIndividualGame'.$game_id, $selectGame, config('app.cache_gamelist_length'));// in minutes
            } else {
                return response()->json([
                    'error' => 'game not found'
                ], 404);
            }
        }
        return $gamespecificCached;
    }

}
