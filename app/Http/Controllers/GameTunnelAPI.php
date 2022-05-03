<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Response;

class GameTunnelAPI extends Controller
{
    public function in(Request $request)
    {

    }

    public function out(Request $request)
    {
       
    }

    public function mixed(Request $request)
    {
        $command = $request->command;
        $urlFullUrl = $request->fullUrl();
        $urlReplaceToReal = str_replace(env('APP_URL').'/api/game_tunnel/mixed/booongo/', 'https://box7-stage.betsrv.com/gate-stage1/gs/', $urlFullUrl);
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

        $resp = curl_exec($curl);
        curl_close($curl);

        return response()->json($resp);
    }

    public function bgamingMixed(Request $request)
    {
        // Add small validator though all should be ok, as most can be considered safe from side of bgaming/ss (with exceptions x)

        // fully working still jlust need go on ssl or remote host to test, currency etc can simply be changed from bottom, would need just to collect the tokens/ssh as players enter, they are valid for 60 minutes in bgaming - which is more then sufficient i've felt 

        // u can recontinue these below extremely easy, overly easy, more over balance amount dont matter, can play in minus etc. dont matter 

        // STILL NEEDS to be edited per game type (provably fair, older games from bgaming (with configurable per line bets) and newwer) 
        // and configured, in rare case games have own configu diff to the above - mainly the very very old may have a slight diff per win bet, but if u take last 50-70 games u will have no issues with just 3 configs

        $game = $request->game_slug;
        $realToken = $request->token; //temp manual added token, simply use demo generator link for bgaming
        $command = $request->command;

        $urlFullUrl = $request->fullUrl();
        $urlReplaceToReal = str_replace(env('APP_URL').'/api/game_tunnel/bgaming/', 'https://bgaming-network.com/api/', $urlFullUrl);
        $url = $urlReplaceToReal;

        Log::debug($urlReplaceToReal);
        $data = $request->getContent();
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json', 'Accept: application/json'));
        curl_setopt($curl, CURLOPT_POST, 1); 
        curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $resp = curl_exec($curl);
        curl_close($curl);
        Log::debug('Response from BGAMING '.$request->command.' method: '.$resp);


        $data_origin = json_decode($resp, true);
        $getSession = \App\Models\GameSessions::where('token_original', $realToken)->first();
        if($getSession) {

        if(isset($data_origin['api_version'])) {
        if($data_origin['api_version'] === "2"){


            // Init is initial load, though can also be intermediary, when you for example switch tabs or are inactive for a while
            if($request->command === 'init') {
                $data_origin['options']['currency']['code'] = $getSession->currency;
            }

            // Spin bet amount (bet minus) should probably be in front of the actual cURL to bgaming above, but as we don't pay any ggr anyway, we might aswell cancel it afterwards for ease
            if($request->command === 'spin') {
                $betAmount = $data_origin['outcome']['bet'];
                $winAmount = $data_origin['outcome']['win'];


                if(isset($data_origin['flow']['purchased_feature']['name'])) {
                    if($data_origin['flow']['purchased_feature']['name'] === 'freespin_chance') {
                        $betAmount = $betAmount * 1.5;
                    }

                }
                $data_origin['options']['currency']['code'] = $getSession->currency;
                $data_origin['balance']['wallet'] = self::generalizedBetCall($getSession->player_id, $getSession->currency, $getSession->gameid, $betAmount, $winAmount);
            }

            if($request->command === 'freespin') {
                $betAmount = $data_origin['outcome']['bet'];
                $winAmount = $data_origin['outcome']['win'];

                $data_origin['options']['currency']['code'] = $getSession->currency;
                $data_origin['balance']['wallet'] = self::generalizedBetCall($getSession->player_id, $getSession->currency, $getSession->gameid, 0, $winAmount);
            }



        } else {
            abort(500, 'BGaming API version not 0 neither api version is 2, new game engine possibly added?');
        }

        $data_origin['balance']['wallet'] = self::generalizedBalanceCall($getSession->player_id, $getSession->currency);
        $data_origin['options']['currency']['code'] = "USD"; 

    } else {
        if($request->command === 'init' || $request->command === 'finish') {
        if(isset($data_origin['balance'])) {
            $data_origin['options']['currency']['code'] = "USD"; 
            $data_origin['balance'] = self::generalizedBalanceCall($getSession->player_id, $getSession->currency);
        }

        }

        if($request->command === 'spin' || $request->command === 'flip') {
                
                // heads or tails game
                if($request->command === 'flip') {
                        $betAmount = (int) $request['options']['bet'];
                        $winAmount = 0;

                        if(isset($data_origin['result']['total'])) {
                            $winAmount =  $data_origin['result']['total'];
                        }
                        if(isset($data_origin['game']['state'])) {
                            if($data_origin['game']['state'] === 'closed') {
                            $data_origin['balance'] = self::generalizedBetCall($getSession->player_id, $getSession->currency, $getSession->gameid, $betAmount, $winAmount);
                            }
                        }
                }

                // Old BGAMING api, where you can set individual betlines when placing bet (* bet amount per betline)
                if(isset($request['extra_data'])) {
                        $multiplier = count($request['options']['bets']);
                        $betAmount = (int) $multiplier * $request['options']['bets']['0'];
                        $winAmount = 0;

                        if(isset($data_origin['result']['total'])) {
                            $winAmount = $data_origin['result']['total'];
                        }
                        //$winAmount = $data_origin['result']['total'];
                        $data_origin['balance'] = self::generalizedBetCall($getSession->player_id, $getSession->currency, $getSession->gameid, $betAmount, $winAmount);

                } elseif(isset($request['options']['skin'])) {
                        $payload = '{"command":"'.$request['command'].'","options":{"bet":'.$request['options']['bet'].', "skin":"'.$request['options']['skin'].'" }}';
                        $multiplier = 1 * $request['options']['bet']; 
                } else {
                        $payload = '{"command":"'.$request['command'].'","options":{"bet":'.$request['options']['bet'].'}}';
                        $multiplier = 1 * $request['options']['bet']; 
                }


        }
        }
    } else {
            abort(404, 'Internal Session not found.');
    }

    return response()->json($data_origin);

    }


    public function generalizedBalanceCall($playerid, $currency, $type = NULL) 
    {
        if($type === NULL) {
            $type = 'internal';
            $player = \App\Models\User::where('id', $playerid)->first();

            if($currency === 'USD') {
                return (int) $retrieveBalance = $player->balance_usd * 100;
            } elseif($currency === 'EUR') {
                return (int) $retrieveBalance = $player->balance_eur * 100;
            } else {
                abort(400, 'balance not supported');            
            }
        } else {
            // Here we will add later on external balance/bet callbacks, outside of own system (for example i have in mind to make 'full api' & 'internal' mode)
            $type = $type;
        }
    }


    public function generalizedBetCall($playerid, $currency, $gameid, $betAmount, $winAmount, $type = NULL) 
    {
        if($type === NULL) {
            $type = 'internal';
            $player = \App\Models\User::where('id', $playerid)->first();

            if($currency === 'USD') {
                $playerCurrentBalance = self::generalizedBalanceCall($playerid, $currency);
                
                // To add error response for insufficient balance on bgaming
                if($betAmount > $playerCurrentBalance) {
                    abort(400, 'balance insufficient: '.$playerCurrentBalance.' bet: '.$betAmount);            
                }

                $processBetCalculation = $playerCurrentBalance - $betAmount;
                $processWinCalculation = $processBetCalculation + $winAmount;
                $transformToOurBalanceFormat = floatval($processWinCalculation / 100);
                $player->update(['balance_usd' => $transformToOurBalanceFormat]);

                return $processWinCalculation;


            } elseif($currency === 'EUR') {
                return (int) $retrieveBalance = $player->balance_eur * 100;
            } else {
                abort(400, 'balance not supported');            
            }
        } else {
            // Here we will add later on external balance/bet callbacks, outside of own system (for example i have in mind to make 'full api' & 'internal' mode)
            $type = $type;
        }
    }
}
