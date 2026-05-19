<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\PaidContent\Http\Controllers\Admin\PaidContentSubscriptionController;
use Packages\ShaunSocial\PaidContent\Http\Controllers\Admin\PaidContentTipController;

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
    Route::get('/paid_contents/subscription/package', [PaidContentSubscriptionController::class, 'package'])->name('paid_content.subscription.package');
    Route::get('/paid_contents/subscription/create_package/{id?}', [PaidContentSubscriptionController::class, 'create_package'])->where('id', '[0-9]+')->name('paid_content.subscription.create_package');
    Route::get('/paid_contents/subscription/delete_package/{id}', [PaidContentSubscriptionController::class, 'delete_package'])->where('id', '[0-9]+')->name('paid_content.subscription.delete_package');
    Route::post('/paid_contents/subscription/store_package', [PaidContentSubscriptionController::class, 'store_package'])->name('paid_content.subscription.store_package');

    Route::get('/paid_contents/tip/packages', [PaidContentTipController::class, 'package'])->name('paid_content.tip.package');
    Route::get('/paid_contents/tip/create_package/{id?}', [PaidContentTipController::class, 'create_package'])->where('id', '[0-9]+')->name('paid_content.tip.create_package');
    Route::post('/paid_contents/tip/store_package', [PaidContentTipController::class, 'store_package'])->name('paid_content.tip.store_package');
    Route::get('/paid_contents/tip/delete_package/{id}', [PaidContentTipController::class, 'delete_package'])->where('id', '[0-9]+')->name('paid_content.tip.delete_package');
    Route::post('/paid_contents/tip/store_order_package', [PaidContentTipController::class, 'store_order_package'])->name('paid_content.tip.store_order_package');
});