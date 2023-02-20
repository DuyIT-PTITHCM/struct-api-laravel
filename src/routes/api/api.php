<?php

use App\Libraries\Core;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

$router->get('/', function () use ($router) {
    return "say oke";
});

/**
 * Version V1.0
 */
Route::group(['middleware' => [], 'prefix' => 'v1.0', 'as' => 'v1.0'], function () use ($router) {
    Core::renderRoutes('api/v1.0', $router);
});
/**
 * Version V2.0
 */
Route::group(['middleware' => [], 'prefix' => 'v2.0', 'as' => 'v2.0'], function () use ($router) {
    Core::renderRoutes('api/v2.0', $router);
});
