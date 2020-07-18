<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserCheckController extends Controller
{
    public function index(){
        $users = new User;
        $is_exist_user = false;
        if (!empty($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $id = $users->whereRaw("`user_id` = $user_id")->value('id');
            if(empty($id)){
                $is_exist_user = false;
            } else {
                $is_exist_user = true;
            }
        } else {
            $is_exist_user = false;
        }
        $result = [
            'result'      => $is_exist_user
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
