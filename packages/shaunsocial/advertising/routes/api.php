<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Advertising\Http\Controllers\Api\AdvertisingController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::post('advertising/store', [AdvertisingController::class, 'store'])->name('advertising_store');
    Route::post('advertising/validate_store', [AdvertisingController::class, 'validate_store'])->name('advertising_validate_store');
    Route::post('advertising/store_edit', [AdvertisingController::class, 'store_edit'])->name('advertising_store_edit');
    Route::post('advertising/store_boot', [AdvertisingController::class, 'store_boot'])->name('advertising_store_boot');
    Route::post('advertising/validate_store_boot', [AdvertisingController::class, 'validate_store_boot'])->name('advertising_validate_store_boot');
    Route::post('advertising/store_stop', [AdvertisingController::class, 'store_stop'])->name('advertising_store_stop');
    Route::post('advertising/store_enable', [AdvertisingController::class, 'store_enable'])->name('advertising_store_enable');
    Route::post('advertising/store_complete', [AdvertisingController::class, 'store_complete'])->name('advertising_store_complete');

    Route::get('advertising/config', [AdvertisingController::class, 'config'])->name('advertising_config');

    Route::get('advertising/get/{status}/{page?}', [AdvertisingController::class, 'get'])->whereIn('status', ['all', 'active', 'stop', 'done'])->where(['page' => '[0-9]+'])->name('advertising_all');
    Route::get('advertising/get_detail/{id}', [AdvertisingController::class, 'get_detail'])->where(['id' => '[0-9]+'])->name('advertising_get_detail');
    Route::get('advertising/get_report/{id}/{page?}', [AdvertisingController::class, 'get_report'])->where(['id' => '[0-9]+'])->where(['page' => '[0-9]+'])->name('advertising_get_report');    
});