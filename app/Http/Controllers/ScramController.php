<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Users;

class ScramController extends Controller
{
    /**
     * UserController constructor.
     */
    public function __construct()
    {
    }

    public function index() {
        return view('pages.index');
    }

    public function register()
    {
        $data = array_map('trim', $_REQUEST);
//var_dump($data);die();

        if (!empty($data['form_type']) AND ($data['form_type']=='register')){
            $login = $data['user_login'];
            // Соединямся с БД
            //$link=mysqli_connect("localhost", "mysql_user", "mysql_password", "testtable");

            $err = '';

            // проверям логин
            if(!preg_match("/^[a-zA-Z0-9]+$/",$login)) {
                $err = "Логин может состоять только из букв английского алфавита и цифр";
            }

            if(strlen($login) < 3 or strlen($login) > 30) {
                $err = "Логин должен быть не меньше 3-х символов и не больше 30";
            }

            // Если нет ошибок, то добавляем в БД нового пользователя
            if(empty($err)) {
                $user = new Users;
                $user->user_login = $login;
                $user->user_password = hash('sha256',hash('sha256',$data['user_password']));// Убираем лишние пробелы и делаем двойное хеширование
                $user->save();

                echo json_encode(['status' => true,'msg' => 'Register Ok. For login -'.$login],JSON_UNESCAPED_UNICODE);
                die();
                //header("Location: login.php"); exit();
            }
            else {
                echo json_encode(['status' => false,'msg' => 'Ошибка: '.$err],JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(['status' => false,'msg' => 'Ошибка: '.$data['form_type']],JSON_UNESCAPED_UNICODE);
        }
    }

    public function auth() {
        $data = array_map('trim', $_REQUEST);

        if (!empty($data['form_type']) AND ($data['form_type']=='auth')){
            // Вытаскиваем из БД запись, у которой логин равняется введенному
            $user = Users::where('user_login', $data['user_login'])->first();
            //var_dump(sha1(sha1(trim($data['user_password']), true)));die();

            // Сравниваем пароли
            if($user->user_password === hash('sha256',hash('sha256',$data['user_password']))) {
                // Генерируем случайное число и шифруем его
                $hash = hash('sha256',$this->makeRandomString());

                // Записываем в БД новый хеш авторизации
                $user->user_hash=$hash;
                $user->save();

                // Ставим куки
                setcookie("id", $user->id, time()+60*60*24*30);
                setcookie("hash", $hash, time()+60*60*24*30,null,null,null,true); // httponly !!!

                // Переадресовываем браузер на страницу проверки нашего скрипта
                header("Location: /scram/check/"); exit();
            } else {
                echo json_encode(['status' => false,'msg' => 'Ошибка: Вы ввели неправильный логин/пароль'],JSON_UNESCAPED_UNICODE);
            }
        } else {
            echo json_encode(['status' => false,'msg' => 'Ошибка: '.$data['form_type']],JSON_UNESCAPED_UNICODE);
        }
    }

    public function check(){
        // Скрипт проверки (cookie)
        if (isset($_COOKIE['id']) and isset($_COOKIE['hash']))
        {
            $user = Users::where('user_hash', $_COOKIE['hash'])->first();

            if($user->user_hash !== $_COOKIE['hash']) {
                setcookie("id", "", time() - 3600*24*30*12, "/");
                setcookie("hash", "", time() - 3600*24*30*12, "/");
                echo json_encode(['status' => false,'msg' => "что-то не получилось"],JSON_UNESCAPED_UNICODE);
            }
            else {
                echo json_encode(['status' => true,'msg' => "Привет, ".$user->user_login.". Всё работает!"],JSON_UNESCAPED_UNICODE);
            }
        }
        else {
            echo json_encode(['status' => false,'msg' => "Включите куки"],JSON_UNESCAPED_UNICODE);
        }
    }


    function verifyNonce() {
        $data = array_map('trim', $_REQUEST);
        //var_dump($data);die();
        if (!empty($data['client_proof'])){
            $client_proof = $data['client_proof'];
            $id = 'User_login';
            $server_nonce = $this->getNonce($id);  // получим нонс из последнего запроса по идентификатору
            //removeNonce($id, $nonce); //удалим нонс чтобы не использовался снова
            //$stored_key = '08deca51df710128b9ef4b03a80a664c6b77d538'; sha1
            $stored_key = 'b3e1e614c321bc20e47b0a260e3c4e3f3a91875d5e71eb2fd85b76f939412115';//sha256

            $testHash = ($client_proof ^ hash('sha256',$server_nonce.$stored_key)) ^ hash('sha256',$server_nonce.$stored_key);

            if ($testHash == $client_proof){
                return json_encode(['status' => true,'msg' => 'authorize ok!'],JSON_UNESCAPED_UNICODE);
            } else {
                return json_encode(['status' => false,'msg' => 'error. $testHash:'.$testHash],JSON_UNESCAPED_UNICODE);
            }
        } else {
            return json_encode(['status' => false,'msg' => "отправьте сюда client_proof в get или post"],JSON_UNESCAPED_UNICODE);
        }
    }

    public function getNonce($id = 1) {
        //$id = //(Identify Request пользователя/сессии и т.п.)
        $server_nonce = hash('sha1', $this->makeRandomString());
        //storeNonce($id, $nonce);
        $server_nonce='our_secret_nonce_from_php_server: '.$server_nonce;

        return json_encode(['status' => true,'msg' => 'ok', 'nonce'=>$server_nonce],JSON_UNESCAPED_UNICODE);//return $nonce to client;
    }

    public function makeRandomString($bits = 256) {
        $bytes = ceil($bits / 8);
        $return = '';
        for ($i = 0; $i < $bytes; $i++) {
            $return .= chr(mt_rand(0, 255));
        }
        return $return;
    }

    public function removeNonce($id, $nonce){
        //тут мы удаляем серверный нонс из БД или из сессии, не важно
        return true;
    }


}