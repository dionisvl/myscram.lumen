<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Users;
use App\Scram\ShaCompute;

/**
 * Базовая реализация SCRAM авторизации.
 *  - клиент делает запрос к серверу для получения server_nonce
 *  - получив snonse клиент генерирует client_proof и отправляет на сервер
 *  - сервер делает проверку авторизации на основе snonse и client_proof и отдает клинту ответ о результате проверки
 *
 *  ps:  snonse генерируется на сервере, сохраняется в бд и удаляется сразу после попытки авторизации
 *  ps2: client_proof передаем в base64 поскольку это бинарные данные и было замечено что
 * при передаче в сыром виде случаются ошибки
 *
 *
 * Class ScramController
 * @package App\Http\Controllers
 */
class ScramController extends Controller
{
    private $hashCount = '2';//2 = sha(sha($string))

    /**
     * UserController constructor.
     */
    public function __construct(){
        \App::singleton(ShaCompute::class,function (){
            return new ShaCompute('sha512','openssl','2',2);
        });

        $this->hashCount = 2;

    }

    public function index(){
        return view('pages.index');
    }


    public function compute($data){
        return \App::make(ShaCompute::class)->compute($data);
    }

    /**
     * Метод для многократного хеширования
     * @param $data
     * @return mixed
     */
    public function hashPassword($data){
        $i = 0;
        while ($i<$this->hashCount){
            $data = $this->compute($data);
            $i++;
        }
        return $data;
    }

    /**
     * прохешируем "левую" часть (sha (p)) уравнения - client_proof = sha (p) XOR sha( s_nonce . sha(sha(p)))
     * Столько раз сколько нужно для совпадения с правой частью , которая может быть многократно хешированна
     * (по факту хешируем левую часть на 1 раз меньше чем правую)
     * @param $data
     * @return mixed
     */
    public function hashLeftPart($data){
        $i = 0;
        while ($i<$this->hashCount-1){
            $data = $this->compute($data);
            $i++;
        }
        return $data;
    }



}