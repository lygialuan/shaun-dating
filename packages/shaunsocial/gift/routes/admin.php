<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Gift\Http\Controllers\Admin\GiftController;

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
    Route::get('/gift', [GiftController::class, 'index'])->name('gift.index');
    Route::get('gift/create/{id?}', [GiftController::class, 'create'])->where('id', '[0-9]+')->name('gift.create');
    Route::post('gift/store', [GiftController::class, 'store'])->name('gift.store');
    Route::post('gift/store_order', [GiftController::class, 'store_order'])->name('gift.store_order');
    Route::get('gift/delete/{id}', [GiftController::class, 'delete'])->where('id', '[0-9]+')->name('gift.delete');
}); 