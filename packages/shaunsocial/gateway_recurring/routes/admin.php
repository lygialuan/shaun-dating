<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\GatewayRecurring\Http\Controllers\Admin\GatewayRecurringController;

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
    Route::get('/gateway_recurring', [GatewayRecurringController::class, 'index'])->name('gateway_recurring.index');
    Route::get('/gateway_recurring/edit/{id}', [GatewayRecurringController::class, 'edit'])->name('gateway_recurring.edit');
    Route::post('/gateway_recurring/store', [GatewayRecurringController::class, 'store'])->name('gateway_recurring.store');
    Route::post('/gateway_recurring/store_order', [GatewayRecurringController::class, 'store_order'])->name('gateway_recurring.store_order');
});