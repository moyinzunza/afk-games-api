<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResourcesController;
use App\Http\Controllers\ResourcesBuildingsController;
use App\Http\Controllers\UpgradesController;
use App\Http\Controllers\CloudMessagingController;

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
})->name('login');

//Resources Api Cron
Route::get('/cron/update_resources', [ResourcesController::class, 'update_resources'])->name('update_resources');

//Clean pushnotification tokens Cron
Route::get('/cron/clean_pushnotification_tokens', [CloudMessagingController::class, 'clean_db_tokens'])->name('clean_db_tokens');

//Upgrade resources buildings Cron
Route::get('/cron/upgrades_resources_buildings', [UpgradesController::class, 'process_resources_buildings'])->name('process_resources_buildings');

//Utils Api
Route::get('/api/v1/get_resources_buildings_prices', [ResourcesBuildingsController::class, 'get_resources_buildings_prices'])->name('get_resources_buildings_prices');
