<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserSubscription\Http\Controllers\Api\UserSubscriptionController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('user_subscription/config', [UserSubscriptionController::class, 'config'])->name('user_subscription_config');

    Route::get('user_subscription/get_current', [UserSubscriptionController::class, 'get_current'])->name('user_subscription_get_current');

    Route::post('user_subscription/store', [UserSubscriptionController::class, 'store'])->name('user_subscription_store');
    Route::post('user_subscription/store_trial', [UserSubscriptionController::class, 'store_trial'])->name('user_subscription_store_trial');
});
