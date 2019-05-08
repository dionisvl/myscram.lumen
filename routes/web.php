<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

/*Route::get('/', function () {
    return view('welcome');
});*/

Route::get('/', function () {
    return \App::version()." <a href='/scram/'>Тут тестовая версия SCRAM авторизации</a>";
});


Route::get('/scram/','ScramController@index');

Route::get('/scram/register/','UserController@register');
Route::post('/scram/register/','UserController@register');

Route::get('/scram/getnonce/','NonceController@getnonce');
Route::post('/scram/getnonce/','NonceController@getnonce');

Route::get('/scram/verifyNonce/','NonceController@verifyNonce');
Route::post('/scram/verifyNonce/','NonceController@verifyNonce');
