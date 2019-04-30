<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;

/**
 * Базовая реализация SCRAM авторизации.
 *  - клиент делает запрос к серверу для получения server_nonce
 *  - получив snonse клиент генерирует client_proof и отправляет на сервер
 *  - сервер делает проверку авторизации на основе snonse и client_proof и отдает клинту ответ о результате проверки
 *
 *  ps:  snonse генерируется на сервере, сохраняется в бд и удаляется сразу после попытки авторизации
 * Class ScramController
 * @package App\Http\Controllers
 */
class ScramController extends Controller
{
    private $favoriteAlgo = 'sha512';
    private $favoriteEncrypt = 'openssl';//hash

    /**
     * @return string
     */
    public function getFavoriteAlgo(): string
    {
        return $this->favoriteAlgo;
    }

    /**
     * @return string
     */
    public function getFavoriteEncrypt(): string
    {
        return $this->favoriteEncrypt;
    }

    /**
     * UserController constructor.
     */
    public function __construct()
    {
    }

    public function index()
    {
        return view('pages.index');
    }

    public function register()
    {
        $data = array_map('trim', $_REQUEST);
        //var_dump($err);die();
        if (!empty($data['user_login'])){
            $login = $data['user_login'];
            $err = '';
            // проверям логин
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $login)) {
                $err = "Логин может состоять только из букв английского алфавита и цифр";
            }
            if (strlen($login) < 3 or strlen($login) > 30) {
                $err = "Логин должен быть не меньше 3-х символов и не больше 30";
            }
        } else $err = "логин пустой";


        // Если нет ошибок, то добавляем в БД нового пользователя
        if (empty($err)) {
            $user = new Users;

            $user->user_login = $login;
            $hashed_password = $this->compute($this->compute($data['user_password']));
            $user->user_password = $hashed_password;
            $user->save();

            return json_encode(['status' => true, 'msg' => 'Register Ok. New user login: ' . $login], JSON_UNESCAPED_UNICODE);
            //header("Location: login.php"); exit();
        } else {
            return json_encode(['status' => false, 'msg' => 'Ошибка: ' . $err], JSON_UNESCAPED_UNICODE);
        }
    }

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


            $client_proof = $data['client_proof'];

            /**
            ($a ^ $b) ^ $b = $a;
            $a = ($a ^ $b) ^ $b
             */

            /*
             * client_proof = sha (p) XOR sha( s_nonce . sha(sha(p)))
             *
             * - Получим левую часть уравнения (sha(p))
             * - извелечем хеш еще раз
             * - сравним с хранимым значением в БД (sha(sha(p)) ($stored_key === $sha_sha_password)
             */
            $sha_password = $this->compute( $server_nonce . $stored_key) ^ $client_proof;

            $sha_sha_password = $this->compute($sha_password);

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
                return json_encode(['status' => false, 'msg' => 'error. $sha_sha_password:' . $sha_sha_password. ' $sha_password:' . $sha_password], JSON_UNESCAPED_UNICODE);
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
                if ($this->compute($this->compute($data['user_password']))!=$stored_key){
                    return json_encode(['status' => false, 'msg' => 'Не верный логин'], JSON_UNESCAPED_UNICODE);
                }
                if (empty($user->server_nonce)) {
                    //$user = New Users;
                    //$user->user_login = $user_login;
                    //$user->user_password = $data['user_password'];
                    $server_nonce = hash('sha256', $this->makeRandomString());
                    $user->server_nonce = $server_nonce;
                    $user->save();
                    return json_encode(['status' => true, 'msg' => 'new nonce created, for $user_login= ' . $user_login, 'nonce' => $server_nonce], JSON_UNESCAPED_UNICODE);
                } else {
                    return json_encode(['status' => true, 'msg' => 'old nonce getted, for $user_login= ' . $user_login, 'nonce' => $user->server_nonce], JSON_UNESCAPED_UNICODE);
                }
            } else return json_encode(['status' => false, 'msg' => 'Пользователь с логином "' . $user_login. '" не найден.'], JSON_UNESCAPED_UNICODE);


        } else {
            $server_nonce = 'our_secret_nonce_from_php_server';
            return json_encode(['status' => true, 'msg' => 'ok, $user_login= ' . $user_login, 'nonce' => $server_nonce], JSON_UNESCAPED_UNICODE);
        }
    }

    private function makeRandomString($bits = 256)
    {
        $bytes = ceil($bits / 8);
        $return = '';
        for ($i = 0; $i < $bytes; $i++) {
            $return .= chr(mt_rand(0, 255));
        }
        return $return;
    }

    private function removeNonce($user_login = 'test_login')
    {
        //тут мы удаляем серверный нонс из БД или из сессии, не важно
            $user = Users::where('user_login', $user_login)->first();
            $user->server_nonce = '';
            $user->save();
            return true;
    }

    private function compute($data)
    {
        $algo = $this->getFavoriteAlgo();
        switch ($this->getFavoriteEncrypt()) {
            case 'openssl':
                return openssl_digest($data, $algo);
            case 'hash':
                return hash($algo,  $data);
        }
        throw new \RuntimeException('No hash function on this platform');
    }
}