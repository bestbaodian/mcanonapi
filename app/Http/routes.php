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
//注册接口
Route::get('register', 'IndexController@register');
//登录接口
Route::get('login', 'IndexController@login');
//修改密码接口
Route::get('upd_pwd', 'IndexController@updpwd');
/*答疑推荐模块接口*/
Route::get('wenda', 'UserController@wenda');
/*答疑最新模块接口*/
Route::get('bestnew', 'UserController@bestnew');
/*答疑未回答接口*/
Route::get('waitreply', 'UserController@waitreply');
//面试资料添加
Route::get('msdata', 'IndexController@msdata');
//个人用户面试资料接口
Route::get('user_show',"IndexController@IC_show");
//所有用户面试资料接口
Route::get('other_show',"IndexController@other_show");
//面试资料搜索
Route::get('ic_search',"IndexController@ic_search");
//方法模块显示数据
Route::get('showffdata', 'IndexController@showffdata');
//答疑模块添加数据
Route::get('add_questions', 'IndexController@add_questions');
/*用户评论*/
Route::get('content', 'UserController@userContent');
/*用户点赞*/
Route::get('zan', 'UserController@userZan');
//个人简历
Route::post('useresume', 'UserController@userResume');
//个人中心 修改上传头像
Route::post('set_headpic', 'UserController@set_headpic');
//实名认证
Route::get('setmsg', 'UserController@setmsg');
//个人中心修改资料接口
Route::get('set_data', 'UserController@set_data');
//方法模块评论展示
Route::get("show_ping","IndexController@f_ping");

