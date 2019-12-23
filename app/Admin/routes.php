<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->get('/wxsendmsg', 'WxMsgController@sendMsg')->name('admin.home');

    $router->resource('users', WxUserController::class);
    $router->resource('msg', WxLiuyanController::class);
    $router->resource('img', WxImgController::class);

    $router->resource('goods', GoodsController::class);//商品管理





});
