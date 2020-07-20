<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserCheckController extends Controller
{
    // ユーザーIDが空の場合はゲストとして適当なユーザーIDを発行してもらう
    // ユーザーIDが空じゃなければそのユーザーの情報を返してもらう(DB未登録時は新規追加)
    public function check(){
        $users = new User;
        $user_id = 0;
        if (!empty($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $userID = $users->whereRaw("`user_id` = $user_id")->value('user_id');
            // テーブルに登録されてなければ登録
            if($userID===null){
                User::insert(['name' => 'user', 'user_id' => strval($user_id), 'point' => "0", 'rank' => 0, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);
            }
        } else {
            $user_id = 1;
        }
        $result = [
            'user_id'      => $user_id
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
