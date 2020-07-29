<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Ver;
use Illuminate\Support\Facades\Schema;
use App\User;
use Illuminate\Support\Facades\DB;
use phpseclib\Crypt\RSA;
use phpseclib\Math\BigInteger;
use Firebase\JWT\JWT;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Signer\Keychain;
use Lcobucci\JWT\Signer\Rsa\Sha256;

class VerController extends Controller
{
    public function index()
    {
        $test_id_token = "eyJraWQiOiI4NjI1WVZCMzgyIiwiYWxnIjoiRVMyNTYifQ.eyJpc3MiOiI4UThCOVYzTDQ1IiwiaWF0IjoxNTk1ODk5NDIyLCJleHAiOjE1OTY1MDQyMjIsImF1ZCI6Imh0dHBzOi8vYXBwbGVpZC5hcHBsZS5jb20iLCJzdWIiOiJqcC5obW4ucHV6emxlIn0.12WCkTLEZ2wQirK0uzRNGmi9slXE9nxDfcY5d4tx6yT5MU4O0__tDCUFIibBSNbaMbyk5cH6QgW3i4rMLsagXA";
        $jwt_token = explode('.', $test_id_token);
        #$str=json_decode($jwt_token[1]);
        $header = base64_decode($jwt_token[0]);
        $payload = base64_decode($jwt_token[1]);
        $signature = base64_decode($jwt_token[2]);
        return $this->create_client_secret_test();

        // $jwt_encode = $this->create_client_secret($payload);
        // $verify_token = $this->verify_token($payload);
        // return $jwt_encode;
        $test=$this->decode_user_token($jwt_encode);
        // $test = $this->create_client_secret($payload);
return $test;
        // DB::table('users')->truncate();
        // return;
        $users = new User;
        return $users->get();
        if (isset($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            var_dump(User::where('user_id',$user_id)->value('point'));
        }
        
        // try {
        //     $version = Ver::first();
        //     $result = [
        //         'result'      => true,
        //         'version'     => $version->version,
        //         'min_version' => $version->min_version
        //     ];
        // } catch(\Exception $e){
            // $result = [
            //     'result' => false,
            //     'error' => [
            //         'messages' => [$e->getMessage()]
            //     ],
            // ];
        //     return $this->resConversionJson($result, $e->getCode());
        // }
        $result = [
            'result' => true
        ];
        return json_encode($result);
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
                return $token;
                break;
            } catch (Exception $e) {
                if($data === $last_key) {
                    return null;
                }
            }
        }
        return $token;
    }

    // Apple の認証後、送られてきたリクエストが正しいものかどうかを検証
    // $code：リダイレクト時にAppleから渡される値(クライアントからもらう)
    private $token_validation_link = 'https://appleid.apple.com/auth/token';
    private function verify_token(){
        try {
            $params = array(
                'grant_type' => 'refresh_token',
                'client_id' => "1524589385",
                'client_secret' => $this->create_client_secret()
            );
            return $this->create_client_secret();

            // エンコードされたクエリ文字列を取得
            $data = http_build_query($params);
            // return $data;


            $header = array(
                "Content-Type: application/x-www-form-urlencoded",
                "Content-Length: ".strlen($data),
                "User-Agent: UA"
            );

            $curl = curl_init('https://appleid.apple.com/auth/token');
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_HTTPHEADER, $header);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            curl_setopt($curl, CURLOPT_TIMEOUT, 5);

            $result = curl_exec($curl);
            $response = json_decode($data, true);
            $status_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);

            if ($status_code !== 200) {
                echo $status_code;
                return false;
            }

            curl_close($curl);
            return $response['id_token'];
        } catch (Exception $e) {
            Log::info($e->getMessage());
            return FALSE;
        }
    }

    // 秘密鍵のファイルとkey_idからclient_secretを作成
    private function create_client_secret()
    {
        $key = file_get_contents("/Users/tamiya/Documents/api_php/AuthKey_RTK2VT6LBC.p8");
        $now = time();
        $expire = $now + (7 * 24 * 60 * 60);

        $payload = array(
            'iss' => env('TEAM_ID', 'team_id'),
            'iat' => $now,
            'exp' => $expire,
            'aud' => 'https://appleid.apple.com',
            'sub' =>  env('CLIENT_ID', 'client_id')
        );

        return JWT::encode($payload, $key, 'ES256', env('KEY_ID', 'key_id'));
    }

    private function create_client_secret_test()
    {
        $now = time();
        $expire = $now + (7 * 24 * 60 * 60);
        $signer = new Sha256();
        $keychain = new Keychain();
        $token = (new Builder())->setIssuer(env('TEAM_ID', 'team_id'))
                                ->setAudience('https://appleid.apple.com')
                                ->setId('unique_id', true)
                                ->setIssuedAt($now)
                                ->setExpiration($expire)
                                ->set('uid', 1)
                                ->sign($signer,  $keychain->getPrivateKey("file:///Users/tamiya/Documents/api_php/AuthKey_RTK2VT6LBC.p8"))
                                ->getToken();
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
