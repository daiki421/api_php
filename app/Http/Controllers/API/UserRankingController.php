<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserRankingController extends Controller
{
    public function rank(){
        $users = new User;
        $result = [];
        $users_info = $users->get();
        foreach($users_info as $user_info){
            $result = [$user_info->user_id => [
                'name'         => $user_info->name,
                'rank'         => $user_info->rank,
                'point'        => $user_info->point
            ]];
        }
        return $this->resConversionJson($result);
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }
}
