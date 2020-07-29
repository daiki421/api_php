<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;

class UserCheckController extends Controller
{
    public function check(){
        $users = new User;
        $result = [];
        $user_id = "";
        $id_token = "";
        // Appleでログインしたユーザー
        if (isset($_POST["user_id"])) {
            $user_id = $_POST["user_id"];
            if(!empty($user_id)){
                $userID = User::where('user_id',$user_id)->value('user_id');

                // テーブルに登録されてなければ新規登録
                if($userID===null){
                    if(isset($_POST["id_token"])){
                        $id_token = $_POST["id_token"];
                        User::insert(['name' => 'user', 'user_id' => strval($user_id), 'point' => "0", 'id_token' => $id_token, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);
                    }
                }
            } else {
                // ゲストユーザーのID発行
                $user_id = file_get_contents("/var/www/api_php/user_id.txt");
                $id_token = "";
                User::insert(['name' => 'user', 'user_id' => strval($user_id), 'point' => "0", 'id_token' => $id_token, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);
                $user_id = (int)$user_id + 1;
                file_put_contents("/var/www/api_php/user_id.txt", $user_id);
            }
        } else {
            // ユーザーIDが送られてこなかった場合はゲストID発行
            $user_id = file_get_contents("/var/www/api_php/user_id.txt");
            $id_token = "";
            User::insert(['name' => 'user', 'user_id' => strval($user_id), 'point' => "0", 'id_token' => $id_token, 'created_at' => date("Y-m-d H:i:s"), 'updated_at' => date("Y-m-d H:i:s")]);
            $user_id = (int)$user_id + 1;
            file_put_contents("/var/www/api_php/user_id.txt", $user_id);
        }
        $result = [
            'user_id'      => strval($user_id),
            'id_token'     => strval($id_token)
        ];
        return $this->resConversionJson($result);
    }

    // Apple の認証後、送られてきたリクエストが正しいものかどうかを検証
    // $code：リダイレクト時にAppleから渡される値(クライアントからもらう)
    private $token_validation_link = 'https://appleid.apple.com/auth/token';
    private function verify_token($code){
        try {
            $params = array(
                'code' => $code,
                'grant_type' => 'authorization_code', // 認証の場合はauthorization_codeを使用
                'redirect_uri'  =>  env('REDIRECT_URL', 'callback'),
                'client_id' => env('CLIENT_ID', 'client_id'),
                'client_secret' => $this->create_client_secret()
            );

            // エンコードされたクエリ文字列を取得
            $data = http_build_query($params);

            $header = array(
                "Content-Type: application/x-www-form-urlencoded",
                "Content-Length: ".strlen($data),
                "User-Agent: UA"
            );

            $curl = curl_init();
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_URL, $this -> token_validation_link);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'POST');
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            $result = curl_exec($curl);
            $response = json_decode($result, true);
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($status_code !== 200) {
                Log::info(curl_error($curl));
                $result_info = [
                    'result'      => false,
                    'id_token'    => null
                ];
                return $result_info;
            }

            curl_close($curl);
            $result_info = [
                'result'      => true,
                'id_token'    => $response['id_token']
            ];
            return $result_info;
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return FALSE;
        }
    }

    // 秘密鍵のファイルとkey_idからclient_secretを作成
    private function create_client_secret()
    {
        // 秘密鍵のファイルの中身を取得
        $key = file_get_contents(env('PRIVATE_KEY_PASS', 'null'));
        $now = time();
        $expire = $now + (7 * 24 * 60 * 60);

        $payload = array(
            'iss' => env('TEAM_ID', 'team_id'),
            'iat' => $now, // 現在時刻
            'exp' => $expire, // 有効期限
            'aud' => 'https://appleid.apple.com', // 固定
            'sub' =>  env('CLIENT_ID', 'client_id')
        );

        return JWT::encode($payload, $key, 'ES256', env('KEY_ID', 'key_id'));
    }

    // JWTでエンコードされたclient_secretでtokenの検証を行う
    // 引数：verify_tokenメソッドの返り値
    private function create_jwk_public_key($jwk)
    {
        $rsa = new RSA();
        $rsa->loadKey(
            [
                'e' => new BigInteger(JWT::urlsafeB64Decode($jwk['e']), 256),
                'n' => new BigInteger(JWT::urlsafeB64Decode($jwk['n']),  256)
            ]
        );
        $rsa->setPublicKey();

        return $rsa->getPublicKey();
    }

    // JWTでデコード
    // $jwt_token = id_token
    private $get_public_key_link = 'https://appleid.apple.com/auth/keys';
    private function decode_user_token($jwt_token)
    {
        // cURLセッションを初期化する
        $curl = curl_init($this->get_public_key_link);

        // cURLのオプション設定
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'GET');
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        // curl実行
        $response = curl_exec($curl);
        // 直近の転送に関する情報を取得
        $info = curl_getinfo($curl);
        // curlセッション終了
        curl_close($curl);

        // code200以外が帰ってきたらnullを返す
        if ($info['http_code'] != 200) {
            return null;
        }
        // 取得した$responseをデコード
        // 第二引数がtrueの場合は連想配列型式になる
        $response = json_decode($response, true);
        $public_keys = $response['keys'];

        if ($public_keys === null) {
            return null;
        }

        // 配列の最後の要素を返す
        $last_key = end($public_keys);
        foreach($public_keys as $data) {
            try {
                // decode action
                $public_key = $this->create_jwk_public_key($data);
                $token = JWT::decode($jwt_token, $public_key, array('RS256'));
                break;
            } catch (Exception $e) {
                if($data === $last_key) {
                    return null;
                }
            }
        }
        return $token;
    }

    private function resConversionJson($result, $statusCode=200)
    {
        if(empty($statusCode) || $statusCode < 100 || $statusCode >= 600){
            $statusCode = 500;
        }
        return response()->json($result, $statusCode, ['Content-Type' => 'application/json'], JSON_UNESCAPED_SLASHES);
    }
}
