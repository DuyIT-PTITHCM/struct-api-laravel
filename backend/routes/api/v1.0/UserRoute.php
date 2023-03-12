<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => [], 'prefix' => 'users'], function () use ($router) {
    $router->get('say-hello',[UserController::class, 'sayHello']);
});

