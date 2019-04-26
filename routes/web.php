<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version()." <a href='/scram/'>Тут тестовая версия SCRAM авторизации</a>";
});

$router->get('/scram/','ScramController@index');

$router->get('/scram/register/','ScramController@register');
$router->get('/scram/auth/','ScramController@auth');


$router->post('/scram/register/','ScramController@register');
$router->post('/scram/auth/','ScramController@auth');
$router->get('/scram/check/','ScramController@check');

$router->get('/scram/getnonce/','ScramController@getnonce');
$router->post('/scram/getnonce/','ScramController@getnonce');

$router->get('/scram/verifyNonce/','ScramController@verifyNonce');
$router->post('/scram/verifyNonce/','ScramController@verifyNonce');
//$router->get('/scram/', [
//    'as' => 'index',
//   'uses' => '\App\Http\Controllers\ScramController@index'
//]);