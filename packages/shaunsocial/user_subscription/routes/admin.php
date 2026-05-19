<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserSubscription\Http\Controllers\Admin\UserSubscriptionController;

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
    Route::get('/user_subscriptions', [UserSubscriptionController::class, 'index'])->name('user_subscription.index');
    Route::get('/user_subscriptions/create/{id?}', [UserSubscriptionController::class, 'create'])->where(['id' => '[0-9]+'])->name('user_subscription.create');
    Route::post('/user_subscriptions/store', [UserSubscriptionController::class, 'store'])->name('user_subscription.store');
    Route::get('/user_subscriptions/delete/{id}', [UserSubscriptionController::class, 'delete'])->where(['id' => '[0-9]+'])->name('user_subscription.delete');

    Route::get('/user_subscriptions/plan/{package_id}', [UserSubscriptionController::class, 'plan'])->where(['package_id' => '[0-9]+'])->name('user_subscription.plan.index');
    Route::get('/user_subscriptions/plan/create/{package_id}/{id?}', [UserSubscriptionController::class, 'create_plan'])->where(['package_id' => '[0-9]+'])->where(['id' => '[0-9]+'])->name('user_subscription.plan.create');
    Route::post('/user_subscriptions/plan/store', [UserSubscriptionController::class, 'store_plan'])->name('user_subscription.plan.store');
    Route::get('/user_subscriptions/plan/delete/{id}', [UserSubscriptionController::class, 'delete_plan'])->where(['id' => '[0-9]+'])->name('user_subscription.plan.delete');

    Route::get('/user_subscriptions/pricing_table/{language?}', [UserSubscriptionController::class, 'pricing_table'])->where('language', '[a-z]+')->name('user_subscription.pricing_table.index');
    Route::post('/user_subscriptions/store_order', [UserSubscriptionController::class, 'store_order'])->name('user_subscription.store_order');
    Route::post('/user_subscriptions/store_plan_order', [UserSubscriptionController::class, 'store_plan_order'])->name('user_subscription.store_plan_order');
    Route::post('/user_subscriptions/store_pricing_table', [UserSubscriptionController::class, 'store_pricing_table'])->name('user_subscription.store_pricing_table');
    Route::get('/user_subscriptions/preview_pricing_table', [UserSubscriptionController::class, 'preview_pricing_table'])->where('language', '[a-z]+')->name('user_subscription.pricing_table.preview');
});