<?php


namespace App\Http\Controllers;


use App\Users;

class UserController extends Controller
{
    public function register()
    {
        $data = array_map('trim', $_REQUEST);
        //var_dump($err);die();

        $login = $data['user_login'];
        $password = $data['user_password'];
        $err = $this->checkLogin($login);
        $err .= $this->checkPassword($password);
        // Если нет ошибок, то добавляем в БД нового пользователя
        if (empty($err)) {
            $this->createUser($login,$data['user_password']);
            return json_encode(['status' => true, 'msg' => 'Register Ok. New user login: ' . $login], JSON_UNESCAPED_UNICODE);
        } else {
            return json_encode(['status' => false, 'msg' => 'Ошибка: ' . $err], JSON_UNESCAPED_UNICODE);
        }
    }

    private function checkLogin(String $str){// проверям логин
        $err = '';
        if (!empty($str)){
            if (!preg_match("/^[a-zA-Z0-9_]+$/", $str)) {
                $err = "Логин может состоять только из букв английского алфавита и цифр";
            }
            if (strlen($str) < 3 or strlen($str) > 30) {
                $err = "Логин должен быть не меньше 3-х символов и не больше 30";
            }
        } else $err = "Логин пустой. ";
        return $err;
    }

    private function checkPassword(String $str){// проверям пароль
        $err = '';
        if (!empty($str)){
            if (strlen($str) < 3 or strlen($str) > 30) {
                $err = "Пароль должен быть не меньше 3-х символов и не больше 30";
            }
        } else $err = "Пароль пустой";
        return $err;
    }

    private function createUser($login,$password){
        $user = new Users;
        $user->user_login = $login;
        //$hashed_password = $this->compute($this->compute($password));
        $hashed_password = \App::make(ScramController::class)->hashPassword($password);
        $user->user_password = $hashed_password;
        $user->save();
    }
}