<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserVerify\Http\Controllers\Admin\UserVerifyController;

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
    Route::get('/user_verify', [UserVerifyController::class, 'index'])->name('user_verify.index');
    Route::get('/user_verify/store_verify/{id}', [UserVerifyController::class, 'store_verify'])->where('id', '[0-9]+')->name('user_verify.store_verify');
    Route::get('/user_verify/store_unverify/{id}', [UserVerifyController::class, 'store_unverify'])->where('id', '[0-9]+')->name('user_verify.store_unverify');
    Route::get('/user_verify/reject/{id}', [UserVerifyController::class, 'reject'])->where('id', '[0-9]+')->name('user_verify.reject');
    Route::post('/user_verify/store_reject', [UserVerifyController::class, 'store_reject'])->name('user_verify.store_reject');
    Route::post('/user_verify/store_manage', [UserVerifyController::class, 'store_manage'])->name('user_verify.store_manage');
});