<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

Route::get('/', function () {
    return view('welcome');
});
//注册
Route::get('register', 'IndexController@register');
//登录
Route::get('login', 'IndexController@login');
//修改密码
Route::get('upd_pwd', 'IndexController@updpwd');