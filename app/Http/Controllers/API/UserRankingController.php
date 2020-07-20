<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserRankingController extends Controller
{
    // スコア保存
    public function set(){
        $users = new User;
        if (!empty($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $userID = $users->whereRaw("`user_id` = $user_id")->value('user_id');
            if($userID===null) {
                // 何もしない
            } else {
                if (!empty($_GET["score"])) {
                    $score = $_GET["score"];
                    // スコア保存
                    User::where('user_id', $userID)->update(['point' => strval($score)]);
                }   
            }
        }
    }

    // スコア取得
    public function get(){
        $users = new User;
        if (!empty($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $score = $users->whereRaw("`user_id` = $user_id")->value('point');
        } else {
            $score = "0";
        }
        $result = [
            'score'      => $score
        ];
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
