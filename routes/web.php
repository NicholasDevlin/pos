<?php

use App\Http\Controllers\OptionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserManagement\DivisionController;
use App\Http\Controllers\UserManagement\LocationController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('login');
});

Route::group(['middleware' => ['prevent-back-history', 'auth']], function () {
    Route::group(['controller' => PageController::class], function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/user/password', 'editUserPassword')->name('user-password.edit');
    });

    Route::resource('options', OptionController::class);

    Route::group(['prefix' => 'user-management'], function () {
        Route::resource('locations', LocationController::class);
        Route::resource('divisions', DivisionController::class);
    });
});
