<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\MasterData\CustomerController;
use App\Http\Controllers\MasterData\ProductCategoryController;
use App\Http\Controllers\MasterData\ProductController;
use App\Http\Controllers\MasterData\UnitOfMeasureController;
use App\Http\Controllers\OptionController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\UserManagement\AuthenticationLogController;
use App\Http\Controllers\UserManagement\BusinessUnitController;
use App\Http\Controllers\UserManagement\LocationController;
use App\Http\Controllers\UserManagement\PermissionController;
use App\Http\Controllers\UserManagement\RoleController;
use App\Http\Controllers\UserManagement\UserController;
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

Route::group(['middleware' => ['prevent-back-history', 'auth', 'show-debugbar']], function () {
    Route::group(['controller' => PageController::class], function () {
        Route::get('/dashboard', 'dashboard')->name('dashboard');
        Route::get('/user/password', 'editUserPassword')->name('user-password.edit');

        Route::group(['prefix' => 'data-initiation'], function () {
            Route::get('/', 'dataInitiationIndex')->name('data-initiation.index');
            Route::post('/download-template', 'dataInitiationDownloadTemplate')->name('data-initiation.download-template');
            Route::post('/import', 'dataInitiationImport')->name('data-initiation.import');
        });
    });

    Route::group(['prefix' => 'master-data'], function () {
        Route::resource('customers', CustomerController::class);
        Route::resource('units-of-measure', UnitOfMeasureController::class);
        Route::resource('product-categories', ProductCategoryController::class);
        Route::resource('products', ProductController::class);
    });

    Route::resource('options', OptionController::class);
    Route::get('logs', ActivityLogController::class)->name('logs');

    Route::group(['prefix' => 'user-management'], function () {
        Route::resource('locations', LocationController::class);
        Route::resource('business-units', BusinessUnitController::class);
        Route::resource('permissions', PermissionController::class);
        Route::resource('roles', RoleController::class);
        Route::resource('users', UserController::class);
        Route::get('logs', AuthenticationLogController::class)->name('user-management.logs');
    });
});
