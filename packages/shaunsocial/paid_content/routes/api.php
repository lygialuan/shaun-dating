<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\PaidContent\Http\Controllers\Api\PaidContentController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::post('paid_content/store_paid_post', [PaidContentController::class, 'store_paid_post'])->name('paid_content_store_paid_post');
    Route::get('paid_content/get_config', [PaidContentController::class, 'get_config'])->name('paid_content_get_config');
    Route::post('paid_content/store_subscriber_user', [PaidContentController::class, 'store_subscriber_user'])->name('paid_content_store_subscriber_user');
    Route::get('paid_content/get_packages', [PaidContentController::class, 'get_packages'])->name('paid_content_get_packages');
    Route::get('paid_content/get_profile_packages/{id}', [PaidContentController::class, 'get_profile_packages'])->where(['id' => '[0-9]+'])->name('paid_content_get_profile_packages');
    Route::post('paid_content/store_user_package', [PaidContentController::class, 'store_user_package'])->name('paid_content_store_user_package');
    Route::get('paid_content/get_earning_report', [PaidContentController::class, 'get_earning_report'])->name('paid_content_get_earning_report');
    Route::get('paid_content/get_earning_transaction', [PaidContentController::class, 'get_earning_transaction'])->name('paid_content_get_earning_transaction');
    Route::get('paid_content/get_user_subscriber', [PaidContentController::class, 'get_user_subscriber'])->name('paid_content_get_user_subscriber');
    Route::get('paid_content/get_subscriber_detail/{id}', [PaidContentController::class, 'get_subscriber_detail'])->where(['id' => '[0-9]+'])->name('subscription_get_subscriber_detail');
    Route::post('paid_content/store_subscriber_cancel', [PaidContentController::class, 'store_subscriber_cancel'])->name('subscription_store_subscriber_cancel');
    Route::post('paid_content/store_subscriber_resume', [PaidContentController::class, 'store_subscriber_resume'])->name('subscription_store_subscriber_resume');
    Route::get('paid_content/get_subscriber_transaction/{id}/{page?}', [PaidContentController::class, 'get_subscriber_transaction'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('subscription_get_subscriber_transaction');
    Route::get('paid_content/get_tip_packages', [PaidContentController::class, 'get_tip_packages'])->name('subscription_get_tip_packages');
    Route::post('paid_content/store_tip', [PaidContentController::class, 'store_tip'])->name('subscription_store_tip');
    Route::get('paid_content/get_my_paid_post/{page?}', [PaidContentController::class, 'get_my_paid_post'])->where(['page' => '[0-9]+'])->name('subscription_get_my_paid_post');
    Route::get('paid_content/get_profile_paid_post/{id}/{page?}', [PaidContentController::class, 'get_profile_paid_post'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('subscription_get_profile_paid_post')->withoutMiddleware('auth:sanctum');
    Route::post('paid_content/store_edit_post', [PaidContentController::class, 'store_edit_post'])->name('subscription_store_edit_post');
});
