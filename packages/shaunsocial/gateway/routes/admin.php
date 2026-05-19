<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Gateway\Http\Controllers\Admin\GatewayController;

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
    Route::get('/gateways', [GatewayController::class, 'index'])->name('gateway.index');
    Route::get('/gateways/edit/{id}', [GatewayController::class, 'edit'])->name('gateway.edit');
    Route::post('/gateways/store', [GatewayController::class, 'store'])->name('gateway.store');
    Route::post('/gateways/store_order', [GatewayController::class, 'store_order'])->name('package.store_order');
});