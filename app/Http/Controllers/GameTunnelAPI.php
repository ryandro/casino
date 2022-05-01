<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;



class GameTunnelAPI extends Controller
{


    /**
     * Show the application dashboard.
     *
     */
    public function in(Request $request)
    {

    }


    /**
     * TEMP- retrieve listing/utillity for provider
     */
    public function out(Request $request)
    {
       
    }



// example https://box7-stage.betsrv.com/gate-stage1/gs/15_golden_eggs/desktop/a1d2131e6f014ed0957cbc76a68588e4/demo/?gsc=login


    public function mixed(Request $request)
    {
        $command = $request->command;

        $urlFullUrl = $request->fullUrl();

        $urlReplaceToReal = str_replace('http://localhost/api/game_tunnel/mixed/booongo/', 'https://box7-stage.betsrv.com/gate-stage1/gs/', $urlFullUrl);
        $url = $urlReplaceToReal;

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = array(
           "Host: box7-stage.betsrv.com",
           "User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:100.0) Gecko/20100101 Firefox/100.0",
           "Accept: */*",
           "Accept-Language: en-US,en;q=0.5",
           "Accept-Encoding: gzip, deflate, br",
           "Content-Type: application/json",
           "DNT: 1",
           "Connection: keep-alive",
           "Sec-Fetch-Dest: empty",
           "Sec-Fetch-Mode: cors",
           "Sec-Fetch-Site: cross-site",
        );
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        //for debug only!
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);

        return response()->json($resp);

    }
}
