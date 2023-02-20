<?php

use App\Http\Controllers\Api\SayHelloController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => [], 'prefix' => 'hehe'], function () use ($router) {
    $router->get('say-hello-v2',[SayHelloController::class, 'sayHello']);
});

