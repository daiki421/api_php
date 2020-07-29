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
        if (isset($_POST["user_id"])) {
            $user_id = $_POST["user_id"];
            $userID = $users->where([['user_id', '>', $user_id]])->value('user_id');
            if($userID===null) {
                // 何もしない
            } else {
                if (isset($_POST["score"])) {
                    $score = $_POST["score"];
                    // スコア保存
                    $users->where('user_id', $userID)->update(['point' => strval($score)]);
                }
            }
        } else {
        }
        $result = [
            'result'      => true
        ];
        return $this->resConversionJson($result);
    }

    // スコア取得
    public function get(){
        $users = new User;
        $score = "0";
        if (isset($_POST["user_id"])) {
            $user_id = $_POST["user_id"];
            $score = $users->where([['user_id', '>', $user_id]])->value('point');
        } else {
            $score = "no_data";
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