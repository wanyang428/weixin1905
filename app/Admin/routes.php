<?php

use Illuminate\Routing\Router;

Admin::routes();

Route::group([
    'prefix'        => config('admin.route.prefix'),
    'namespace'     => config('admin.route.namespace'),
    'middleware'    => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index')->name('admin.home');
    $router->resource('users', WxUserController::class);
<<<<<<< HEAD
    $router->resource('msg', WxLiuyanController::class);
    
=======


>>>>>>> 98d3df064c2ee7cf7114ece277eae758fefa11a9
});
