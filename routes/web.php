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

Route::get('/', function () {
    return view('welcome');
});


Route::get('/wx','Weixin\WeixinController@wx');
Route::post('/wx','Weixin\WeixinController@receiv');
Route::get('/wx/picture','Weixin\WeixinController@picture');
Route::get('/caidan','Weixin\WeixinController@caidan');


/*微信公众号*/
Route::get('/vote','VoteController@index'); //微信投票
