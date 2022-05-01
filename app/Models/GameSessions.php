<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Spatie\QueryBuilder\QueryBuilder;

class GameSessions extends Model
{
    use HasFactory;

    protected $table = 'gamesessions';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    

    protected $fillable = [
        'token_internal',
        'player_id',
        'game_id',
        'extra_meta',
        'expired_bool',
        'created_at',
        'updated_at',
    ];

    protected $hidden = [
        'token_original',

    ];

    public static function dataQueryGameSessions() {
        // only showing for logged in users & only game sessions assigned/owned by the specific user, you can add here admin routes and what not if u wish to make overviews/admin panel

        $data = QueryBuilder::for(GameSessions::class)->allowedFields(['token_internal', 'player_id', 'game_id', 'extra_meta'])->where('player_id', auth()->user()->id)->allowedSorts('id')->paginate()->appends(request()->query());

        return $data;
    }


}
