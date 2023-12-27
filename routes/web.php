<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ResourcesBuildingsController;
use App\Http\Controllers\Api\CronController;
use App\Http\Controllers\Api\TestController;

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

//routes for webviews
Route::get('/', function () {
    return view('home');
})->name('home');

Route::get('/login', function () {
    return view('login');
})->name('login');

Route::get('/resources', function () {
    return view('resources');
})->name('resources');

Route::get('/facilities', function () {
    return view('facilities');
})->name('facilities');

Route::get('/technologies', function () {
    return view('technologies');
})->name('technologies');

Route::get('/army', function () {
    return view('army');
})->name('army');

Route::get('/defense', function () {
    return view('defense');
})->name('defense');

Route::get('/army_movement', function () {
    return view('army_movement');
})->name('army_movement');

Route::get('/galaxy', function () {
    return view('map');
})->name('map');

//Resources Api Cron
Route::get('/cron/update_resources', [CronController::class, 'update_resources'])->name('update_resources');

//Clean pushnotification tokens Cron
Route::get('/cron/clean_pushnotification_tokens', [CronController::class, 'clean_db_tokens'])->name('clean_db_tokens');

//Upgrade resources buildings Cron
Route::get('/cron/process_upgrades', [CronController::class, 'process_upgrades'])->name('process_upgrades');

//Process army line
Route::get('/cron/process_army_line', [CronController::class, 'process_army_line'])->name('process_army_line');

//Process army Movements
Route::get('/cron/process_army_movements', [CronController::class, 'process_army_movements'])->name('process_army_movements');

//Inactivate Accounts Cron
Route::get('/cron/inactivate_accounts', [CronController::class, 'inactivate_accounts'])->name('inactivate_accounts');

//Utils Api
Route::get('/api/v1/get_resources_buildings_prices', [ResourcesBuildingsController::class, 'get_resources_buildings_prices'])->name('get_resources_buildings_prices');
