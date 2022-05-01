<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use \Carbon\Carbon; 

class Gamelist extends Model
{
    use HasFactory;

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
        'short_desc', 
        'funplay'
    ];

    protected $hidden = [
        'open',
        'api_extension',
        'api_origin_id',
        'api_extra'
    ];
    /*
    protected $castable = [
        'created_at' => date(),
        'updated_at' => date(),
        'released_at' => date(),
    ];
    */

    public static function cachedGamelist() {
        $gamelistResponse = Cache::get('cachedGamelist');

        if(env('APP_ENV' === 'local')) {
                Artisan::command('optimize:clear'); 
        }

        if(!$gamelistResponse) {
            $gamelistResponse = self::all();

            $gamelist = Cache::put('cachedGamelist', $gamelistResponse, 10);
        } 

        return $gamelistResponse;
    }

    public static function mixJSONandDBEntries(Request $request) 
    {
        /* Finish later, after testing which frontend hax suit best as probably need extra fields */
        return self::cachedGamelist();
        /* ^^^^^^^^ */
    }



}
