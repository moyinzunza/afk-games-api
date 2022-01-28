<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\AccountApiController;
use App\Http\Controllers\BuildingsController;

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

//Resources Api
Route::get('/api/v1/update_resources', [ResourcesController::class, 'update_resources'])->name('update_resources');

//Buildings Api
Route::post('/api/v1/upgrade_resources_building', [BuildingsController::class, 'upgrade_resources_building'])->name('upgrade_resources_building');
Route::get('/api/v1/get_resources_buildings_prices', [BuildingsController::class, 'get_resources_buildings_prices'])->name('get_resources_buildings_prices');

