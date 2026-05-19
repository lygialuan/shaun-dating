<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Gift\Http\Controllers\Api\GiftController;

Route::group(['prefix' => 'api/gift/'], function () {
    Route::group(['middleware' => ['auth:sanctum', 'api']], function () {
        Route::post('send', [GiftController::class, 'send'])->name('gift_send');
        Route::get('list/{page?}', [GiftController::class, 'list'])->where(['page' => '[0-9]+'])->name('gift_list');
        Route::get('gift_received/{id}/{page?}', [GiftController::class, 'gift_received'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('gift_received');
    }); 
});