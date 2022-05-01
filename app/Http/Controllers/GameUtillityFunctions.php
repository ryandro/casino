<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;
use \Carbon\Carbon; 

class GameUtillityFunctions extends Controller
{
    //
    public static $booongo_apikey = 'hj1yPYivJmIX4X1I1Z57494re';

        // Using TIGER Mafia apikey

    public static function retrieveGamesBooongo() {

        $url = "https://gate-stage.betsrv.com/op/tigergames-stage/api/v1/game/list";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
           "Content-Type: application/json",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $data = json_encode(array(
            "api_token" => "hj1yPYivJmIX4X1I1Z57494re",
          ));

        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        $resp = json_decode($resp, true);

        foreach($resp['items'] as $gameItem)
        {
            if(isset($gameItem['game_id'])) {
            $transformInFormat[] = array(
                'game_id' => $gameItem['game_name'],
                'fullName' => $gameItem['i18n']['en']['title'],
                'thumbnail' => urldecode($gameItem['i18n']['en']['banner_path']),
                'provider' => 'booongo',
                'open' => 1,
                'api_origin_id' => $gameItem['game_id'].'++'.$gameItem['game_name'],
                'api_extension' => 'tigermafia_booongo',
                'api_extra' => NULL,
                'release_date' => $gameItem['release_date'],
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            );


            
        }
        }

        Log::notice($transformInFormat);

        return json_encode($transformInFormat);


    }


}
