<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserVerify\Http\Controllers\Api\UserVerifyController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('user_verify/get_files', [UserVerifyController::class, 'get_files'])->name('user_verify_get_files');
    Route::post('user_verify/upload_file', [UserVerifyController::class, 'upload_file'])->name('user_verify_upload_file');
    Route::post('user_verify/delete_file', [UserVerifyController::class, 'delete_file'])->name('user_verify_delete_file');
    Route::post('user_verify/store_request', [UserVerifyController::class, 'store_request'])->name('user_verify_store_request');
});
