<?php

namespace App\Http\Controllers;


use App\Http\Controllers\Controller;
use App\Http\Controllers\ScramController;
use App\Scram\RandomString;
use App\Users;

class NonceController extends Controller
{
    function verifyNonce()
    {
        $data = array_map('trim', $_REQUEST);
        //var_dump($data);die();
        if (!empty($data['client_proof'])) {

            if (empty($data['user_login'])) {
                $user_login = 'test_login';
            } else $user_login = $data['user_login'];

            // Вытаскиваем из БД запись, у которой логин равняется введенному
            $user = Users::where('user_login', $user_login)->first();
            if (empty($user)) {
                return json_encode(['status' => false, 'msg' => "Пользователь с таким логином не найден"], JSON_UNESCAPED_UNICODE);
            }

            $stored_key = $user->user_password;//'b3e1e614c321bc20e47b0a260e3c4e3f3a91875d5e71eb2fd85b76f939412115';//sha256
            $server_nonce = $this->getNonce($user_login);  // получим нонс из последнего запроса по идентификатору

            $server_nonce = json_decode($server_nonce);
            $server_nonce = $server_nonce->nonce;


            $client_proof = base64_decode($data['client_proof']);
            /*
             * client_proof = sha (p) XOR sha( s_nonce . sha(sha(p)))
             *
             * - Получим левую часть уравнения (sha(p))
             * - извелечем хеш еще раз
             * - сравним с хранимым значением в БД (sha(sha(p)) ($stored_key === $sha_sha_password)
             */
            $computer = new ScramController();//'sha512','openssl',1,2
            $sha_password = $computer->compute( $server_nonce . $stored_key) ^ $client_proof;
            $sha_sha_password = $computer->hashLeftPart($sha_password);

//            var_dump('-------------------------------------------------------------------');
//            var_dump('right_part: ' . openssl_digest($server_nonce . $stored_key,'sha512'));
//            var_dump('$client_proof: ' . $client_proof);
//            var_dump('$client_proof B64: ' . base64_encode($client_proof));
//            var_dump('$server_nonce:'.$server_nonce);
//
//            var_dump('$sha_password:'.$sha_password);
//            var_dump('$sha_sha_password:'.$sha_sha_password);
//            var_dump('$stored_key: ' . $stored_key);
//            var_dump('-********************************************************************-');
//            print_r('-********************************************************************-');
//            die();



            $this->removeNonce($user_login); //удалим нонс чтобы не использовался снова

            if ($stored_key === $sha_sha_password) {
                return json_encode(['status' => true, 'msg' => 'authorize ok! Your login: ' . $user_login], JSON_UNESCAPED_UNICODE);
            } else {
                return json_encode(['status' => false, 'msg' => 'error. $sha_sha_password:' . $sha_sha_password. ' $stored_key:' . $stored_key], JSON_UNESCAPED_UNICODE);
            }
        } else {
            return json_encode(['status' => false, 'msg' => "отправьте сюда client_proof в get или post"], JSON_UNESCAPED_UNICODE);
        }
    }

    public function getNonce($user_login = 'test_login')
    {
        $data = array_map('trim', $_REQUEST);
        if (!empty($data['user_login'])) {
            $user_login = $data['user_login'];
        }
        if ($user_login != 'test_login') {
            $user = Users::where('user_login', $user_login)->first();
            if (!empty($user)) {
                $stored_key = $user->user_password;

                $password = $data['user_password'];

                $computer = new ScramController();
                if ( $computer->hashPassword($password) != $stored_key){
                    return json_encode(['status' => false, 'msg' => 'Не верный пароль'], JSON_UNESCAPED_UNICODE);
                }
                if (empty($user->server_nonce)) {
                    $server_nonce = $this->createNonceTo($user);
                    return json_encode(['status' => true, 'msg' => 'new nonce created, for $user_login= ' . $user_login, 'nonce' => $server_nonce], JSON_UNESCAPED_UNICODE);
                } else {
                    return json_encode(['status' => true, 'msg' => 'old nonce received, for $user_login= ' . $user_login, 'nonce' => $user->server_nonce], JSON_UNESCAPED_UNICODE);
                }
            } else return json_encode(['status' => false, 'msg' => 'Пользователь с логином "' . $user_login. '" не найден.'], JSON_UNESCAPED_UNICODE);


        } else {
            $server_nonce = 'our_secret_nonce_from_php_server';
            return json_encode(['status' => true, 'msg' => 'ok, $user_login= ' . $user_login, 'nonce' => $server_nonce], JSON_UNESCAPED_UNICODE);
        }
    }

    private function removeNonce($user_login = 'test_login'){
        //тут мы удаляем серверный нонс из БД или из сессии, не важно
        $user = Users::where('user_login', $user_login)->first();
        $user->server_nonce = '';
        $user->save();

        return true;
    }

    private function createNonceTo($user){
        $server_nonce = (new RandomString())->handle();
        $user->server_nonce = $server_nonce;
        $user->save();
        return $server_nonce;
    }

}