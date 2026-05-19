<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Advertising\Http\Controllers\Admin\AdvertisingController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/
Route::group(['prefix' => env('APP_ADMIN_PREFIX', 'admin'), 'as' => 'admin.', 'middleware' => ['web', 'is.admin']], function () {
    Route::get('/advertisings', [AdvertisingController::class, 'index'])->name('advertising.index');
    Route::get('/advertisings/detail/{id}', [AdvertisingController::class, 'detail'])->where(['id' => '[0-9]+'])->name('advertising.detail');
    Route::get('/advertisings/store_enable/{id}', [AdvertisingController::class, 'store_enable'])->where(['id' => '[0-9]+'])->name('advertising.store_enable');
    Route::get('/advertisings/store_stop/{id}', [AdvertisingController::class, 'store_stop'])->where(['id' => '[0-9]+'])->name('advertising.store_stop');
    Route::get('/advertisings/store_complete/{id}', [AdvertisingController::class, 'store_complete'])->where(['id' => '[0-9]+'])->name('advertising.store_complete');
});