<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public static function helperArrayToRandom($array, $amount) {
        return collect($array)->random($amount);
    }

    public static function helperArrayToCollection($array, $amount = NULL) {
        return collect($array)->all();
    }
    public static function helperArrayWhereGet($array, $value, $key) {
        return collect($array)->where($value, '=', $key);
    }


}
