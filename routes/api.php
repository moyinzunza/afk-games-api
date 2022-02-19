<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\HandshakeController;
use App\Http\Controllers\Api\ResourcesBuildingsController;
use App\Http\Controllers\Api\HomeController;
use App\Http\Controllers\Api\ModulesController;
use App\Http\Controllers\Api\FacilitiesController;
/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::group([
    'prefix' => ''
], function () {
    Route::post('login', [AuthController::class, 'login']);
    Route::post('signup', [AuthController::class, 'signUp']);

    Route::group([
      'middleware' => 'auth:api'
    ], function() {
        //handshake
        Route::post('handshake', [HandshakeController::class, 'handshake']);

        //home
        Route::get('home',[HomeController::class, 'get_home_data']);

        //user managment
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('user', [AuthController::class, 'user']);

        //Modules
        Route::get('get_modules', [ModulesController::class, 'get_modules']);

        //Resources 
        Route::get('module/{module_id}/resources', [ResourcesBuildingsController::class, 'get_module_resources']);
        Route::post('module/{module_id}/resources', [ResourcesBuildingsController::class, 'upgrade_resources_building'])->name('upgrade_resources_building');

        //Facilities
        Route::get('module/{module_id}/facilities', [FacilitiesController::class, 'get_module_facilities']);

    });
});
