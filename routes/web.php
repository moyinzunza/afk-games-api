<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ResourcesController;

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

Route::get('/api/v1/update_resources', [ResourcesController::class, 'update_resources'])->name('update_resources');
Route::get('/api/v1/get_resources/{user_id}', [ResourcesController::class, 'get_resources'])->name('get_resources');