<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserPage\Http\Controllers\Api\UserPageController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('user_page/get_categories', [UserPageController::class, 'get_categories'])->name('user_page_get_categories');
    Route::get('user_page/get_switch/{page?}', [UserPageController::class, 'get_switch'])->where(['page' => '[0-9]+'])->name('user_page_get_switch');
    Route::get('user_page/get_my/{page?}', [UserPageController::class, 'get_my'])->where(['page' => '[0-9]+'])->name('user_page_get_my');
    Route::get('user_page/get_admin/{page?}', [UserPageController::class, 'get_admin'])->where(['page' => '[0-9]+'])->name('user_page_get_admin');
    Route::post('user_page/store', [UserPageController::class, 'store'])->name('user_page_store');
    Route::post('user_page/switch_page', [UserPageController::class, 'switch_page'])->name('user_page_switch_page');
    Route::post('user_page/login_back', [UserPageController::class, 'login_back'])->name('user_page_login_back');
    Route::post('user_page/add_admin', [UserPageController::class, 'add_admin'])->name('user_page_add_admin');
    Route::post('user_page/transfer_owner', [UserPageController::class, 'transfer_owner'])->name('user_page_transfer_owner');
    Route::post('user_page/remove_admin', [UserPageController::class, 'remove_admin'])->name('user_page_remove_admin');

    Route::post('user_page/store_privacy', [UserPageController::class, 'store_privacy'])->name('user_page_store_privacy');
    Route::post('user_page/store_profile', [UserPageController::class, 'store_profile'])->name('user_page_store_profile');
    Route::post('user_page/store_description', [UserPageController::class, 'store_description'])->name('user_page_store_description');
    Route::post('user_page/store_address', [UserPageController::class, 'store_address'])->name('user_page_store_address');
    Route::post('user_page/store_phone_number', [UserPageController::class, 'store_phone_number'])->name('user_page_store_phone_number');
    Route::post('user_page/store_email', [UserPageController::class, 'store_email'])->name('user_page_store_email');
    Route::post('user_page/store_category', [UserPageController::class, 'store_category'])->name('user_page_store_category');
    Route::post('user_page/store_hashtag', [UserPageController::class, 'store_hashtag'])->name('user_page_store_hastag');
    Route::get('user_page/get_prices', [UserPageController::class, 'get_prices'])->name('user_page_get_prices');
    Route::post('user_page/store_price', [UserPageController::class, 'store_price'])->name('user_page_store_price');
    Route::post('user_page/store_websites', [UserPageController::class, 'store_websites'])->name('user_page_store_websites');
    Route::get('user_page/get_hours', [UserPageController::class, 'get_hours'])->name('user_page_get_hours');
    Route::post('user_page/store_hour', [UserPageController::class, 'store_hour'])->name('user_page_store_hour');
    Route::post('user_page/store_enable_review', [UserPageController::class, 'store_enable_review'])->name('user_page_store_enable_review');

    Route::get('user_page/get_all', [UserPageController::class, 'get_all'])->name('user_page_get_all')->withoutMiddleware('auth:sanctum');
    Route::get('user_page/get_trending/{page?}', [UserPageController::class, 'get_trending'])->where(['page' => '[0-9]+'])->name('user_page_get_trending')->withoutMiddleware('auth:sanctum');
    Route::get('user_page/get_for_you/{page?}', [UserPageController::class, 'get_for_you'])->where(['page' => '[0-9]+'])->name('user_page_get_for_you');
    Route::get('user_page/get_following/{page?}', [UserPageController::class, 'get_following'])->where(['page' => '[0-9]+'])->name('user_page_get_following');

    Route::post('user_page/store_review', [UserPageController::class, 'store_review'])->name('user_page_store_review');
    Route::get('user_page/get_reviews/{page_id}/{page?}', [UserPageController::class, 'get_reviews'])->where(['page_id' => '[0-9]+','page' => '[0-9]+'])->name('user_page_get_reviews')->withoutMiddleware('auth:sanctum');

    Route::get('user_page/get_report_overview', [UserPageController::class, 'get_report_overview'])->name('user_page_get_report_overview');
    Route::get('user_page/get_report_audience', [UserPageController::class, 'get_report_audience'])->name('user_page_get_report_audience');

    Route::get('user_page/get_notify_setting', [UserPageController::class, 'get_notify_setting'])->name('user_page_get_notify_setting');
    Route::post('user_page/store_notify_setting', [UserPageController::class, 'store_notify_setting'])->name('user_page_store_notify_setting');

    Route::get('user_page/suggest_user_for_admin/{text}', [UserPageController::class, 'suggest_user_for_admin'])->name('user_page_suggest_user_for_admin');
    Route::get('user_page/suggest_user_for_transfer/{text}', [UserPageController::class, 'suggest_user_for_transfer'])->name('user_page_suggest_user_for_transfer');

    Route::post('user_page/store_general_setting', [UserPageController::class, 'store_general_setting'])->name('user_page_store_general_setting');
    Route::post('user_page/delete', [UserPageController::class, 'delete'])->name('user_page_delete');

    Route::get('user_page/get_feature_packages', [UserPageController::class, 'get_feature_packages'])->name('user_page_get_feature_packages');
    Route::post('user_page/store_feature', [UserPageController::class, 'store_feature'])->name('user_page_store_feature');

    Route::get('user_page/get_feature', [UserPageController::class, 'get_feature'])->name('user_page_get_feature');
    Route::get('user_page/search_post', [UserPageController::class, 'search_post'])->name('user_page_search_post')->withoutMiddleware('auth:sanctum');
    Route::get('user_page/get_post_with_hashtag/{id}/{name}/{page?}', [UserPageController::class, 'get_post_with_hashtag'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('user_page_get_post_with_hashtag')->withoutMiddleware('auth:sanctum');
});
