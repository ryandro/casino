<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use \Carbon\Carbon;

class Presets extends Model
{
    use HasFactory;

    protected $table = 'presets';
    protected $timestamp = true;
    protected $primaryKey = 'id';
    
    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'preset',
        'preset_desc',
        'preset_value',
        'preset_bool',
        'created_at',
        'updated_at',
    ];
    

    public static function cachedPreset() {
        $cachedPresetResponse = Cache::get('cachedPreset');

        if(env('APP_ENV' === 'local')) {
                Artisan::command('optimize:clear'); 
        }

        if(!$cachedPresetResponse) {
            $cachedPresetResponse = self::all();
            $setCache = Cache::put('cachedPreset', $cachedPresetResponse, config('app.cache_presets_length')); // in minutes cache
        }

        return $cachedPresetResponse;
    }

    public static function returnPresetValue($preset_id, $preset_value = NULL) {

        $selectPreset = self::cachedPreset()->where('preset_id', '=', $preset_id)->first();

        if($selectPreset) {
            return $selectPreset->preset_value;
        } else {
            // Lets later create auto creation in case preset not found (with options), however this became issue in own software before, with injection (shazazm xD fucking settings.php)
            abort(500, 'Preset not found');
        }
    }

    public static function returnBoolValue($preset_id, $preset_value = NULL) {
        $selectPreset = self::cachedPreset()->where('preset_id', '=', $preset_id)->first();

        if($selectPreset) {
            if($selectPreset->preset_bool === 1) {
                return true;
            } else {
                return false;
            }
        } else {
            // Lets later create auto creation in case preset not found (with options), however this became issue in own software before, with injection (shazazm xD fucking settings.php)
            abort(500, 'Preset not found');
        }
    }

}
