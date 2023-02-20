<?php

use App\Http\Controllers\Api\SayHelloController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => [], 'prefix' => 'users'], function () use ($router) {
    $router->get('say-hello',[SayHelloController::class, 'sayHello']);
});

