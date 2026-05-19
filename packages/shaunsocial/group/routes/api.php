<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Group\Http\Controllers\Api\GroupController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('groups/get_categories', [GroupController::class, 'get_categories'])->name('group_get_categories')->withoutMiddleware('auth:sanctum');
    Route::post('groups/store', [GroupController::class, 'store'])->name('group_store');
    Route::get('groups/get_profile/{id}', [GroupController::class, 'get_profile'])->where(['id' => '[0-9]+'])->name('group_get_profile')->withoutMiddleware('auth:sanctum');

    Route::post('groups/store_rule', [GroupController::class, 'store_rule'])->name('group_store_rule');
    Route::get('groups/get_rule/{id}', [GroupController::class, 'get_rule'])->where(['id' => '[0-9]+'])->name('group_get_rule')->withoutMiddleware('auth:sanctum');
    Route::post('groups/store_rule_order', [GroupController::class, 'store_rule_order'])->where(['id' => '[0-9]+'])->name('group_store_rule_order');
    Route::post('groups/delete_rule', [GroupController::class, 'delete_rule'])->name('group_delete_rule');
    Route::post('groups/upload_cover', [GroupController::class, 'upload_cover'])->name('group_upload_cover');

    Route::post('groups/store_name', [GroupController::class, 'store_name'])->name('group_store_name');
    Route::post('groups/store_description', [GroupController::class, 'store_description'])->name('group_store_description');
    Route::post('groups/store_category', [GroupController::class, 'store_category'])->name('group_store_category');
    Route::post('groups/store_hashtag', [GroupController::class, 'store_hashtag'])->name('group_store_hashtag');
    Route::post('groups/store_type_private', [GroupController::class, 'store_type_private'])->name('group_store_private');

    Route::post('groups/store_setting', [GroupController::class, 'store_setting'])->name('group_store_setting');
    Route::post('groups/store_join', [GroupController::class, 'store_join'])->name('group_store_join');
    Route::post('groups/remove_join_request', [GroupController::class, 'remove_join_request'])->name('group_remove_join_request');
    Route::post('groups/store_leave', [GroupController::class, 'store_leave'])->name('group_store_leave');

    Route::get('groups/get_explore/{page?}', [GroupController::class, 'get_explore'])->where(['page' => '[0-9]+'])->name('group_get_explore')->withoutMiddleware('auth:sanctum');
    Route::get('groups/get_your_feed/{page?}', [GroupController::class, 'get_your_feed'])->where(['page' => '[0-9]+'])->name('group_get_your_feed');
    Route::get('groups/get_all', [GroupController::class, 'get_all'])->name('group_get_all')->withoutMiddleware('auth:sanctum');
    Route::get('groups/get_for_you/{page?}', [GroupController::class, 'get_for_you'])->where(['page' => '[0-9]+'])->name('group_get_for_you');

    Route::get('groups/get_post/{id}/{page?}', [GroupController::class, 'get_post'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_post')->withoutMiddleware('auth:sanctum');
    Route::get('groups/get_post_with_hashtag/{id}/{name}/{page?}', [GroupController::class, 'get_post_with_hashtag'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_post_with_hashtag')->withoutMiddleware('auth:sanctum');
    Route::get('groups/get_media/{id}/{page?}', [GroupController::class, 'get_media'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_media')->withoutMiddleware('auth:sanctum');

    Route::get('groups/get_admin/{id}/{page?}', [GroupController::class, 'get_admin'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_admin')->withoutMiddleware('auth:sanctum');
    Route::get('groups/get_members/{id}', [GroupController::class, 'get_members'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_members')->withoutMiddleware('auth:sanctum');
    Route::post('groups/remove_member', [GroupController::class, 'remove_member'])->name('group_remove_member');
    Route::get('groups/get_blocks/{id}', [GroupController::class, 'get_blocks'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_blocks');

    Route::post('groups/store_block', [GroupController::class, 'store_block'])->name('group_store_block');
    Route::post('groups/remove_block', [GroupController::class, 'remove_block'])->name('group_remove_block');
    
    Route::post('groups/store_notify_setting', [GroupController::class, 'store_notify_setting'])->name('group_store_notify_setting');

    Route::post('groups/store_pin', [GroupController::class, 'store_pin'])->name('group_store_pin');

    Route::get('groups/get_join_request/{id}', [GroupController::class, 'get_join_request'])->where(['id' => '[0-9]+'])->name('group_get_join_request');
    Route::post('groups/accept_join_request', [GroupController::class, 'accept_join_request'])->name('group_accept_join_request');
    Route::post('groups/accept_multi_join_request', [GroupController::class, 'accept_multi_join_request'])->name('group_accept_multi_join_request');
    Route::post('groups/delete_join_request', [GroupController::class, 'delete_join_request'])->name('group_delete_join_request');
    Route::post('groups/delete_multi_join_request', [GroupController::class, 'delete_multi_join_request'])->name('group_delete_multi_join_request');

    Route::get('groups/get_my_post_pending/{id}/{page?} ', [GroupController::class, 'get_my_post_pending'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('group_get_my_post_pending');
    Route::post('groups/delete_my_post_pending', [GroupController::class, 'delete_my_post_pending'])->name('group_delete_my_post_pending');
    Route::get('groups/get_post_pending/{id}', [GroupController::class, 'get_post_pending'])->name('group_get_post_pending');
    Route::post('groups/delete_post_pending', [GroupController::class, 'delete_post_pending'])->name('group_delete_post_pending');
    Route::post('groups/accept_post_pending', [GroupController::class, 'accept_post_pending'])->name('group_accept_post_pending');

    Route::get('groups/search_user_for_admin/{id}/{text}', [GroupController::class, 'search_user_for_admin'])->name('group_search_user_for_admin');
    Route::post('groups/store_transfer_owner', [GroupController::class, 'store_transfer_owner'])->name('group_store_transfer_owner');
    Route::post('groups/add_admin', [GroupController::class, 'add_admin'])->name('group_add_admin');
    Route::post('groups/remove_admin', [GroupController::class, 'remove_admin'])->name('group_remove_admin');

    Route::get('groups/get_report_overview/{id}', [GroupController::class, 'get_report_overview'])->where(['id' => '[0-9]+'])->name('group_get_report_overview');
    Route::get('groups/get_report_chart/{id}', [GroupController::class, 'get_report_chart'])->where(['id' => '[0-9]+'])->name('group_get_report_chart');

    Route::get('groups/get_admin_manage_config/{id}', [GroupController::class, 'get_admin_manage_config'])->where(['id' => '[0-9]+'])->name('group_get_admin_manage_config');
    Route::get('groups/get_user_manage_config/{id}', [GroupController::class, 'get_user_manage_config'])->where(['id' => '[0-9]+'])->name('group_get_user_manage_config');

    Route::post('groups/store_hide', [GroupController::class, 'store_hide'])->name('group_store_hide');
    Route::post('groups/store_open', [GroupController::class, 'store_open'])->name('group_store_open');
    Route::get('groups/get_manage_group', [GroupController::class, 'get_manage_group'])->name('group_get_manage_group');
    Route::get('groups/get_joined/{page?}', [GroupController::class, 'get_joined'])->where(['id' => '[0-9]+'])->name('group_get_joined');

    Route::post('groups/delete', [GroupController::class, 'delete'])->name('group_delete');
    Route::get('groups/search_post', [GroupController::class, 'search_post'])->name('group_search_post')->withoutMiddleware('auth:sanctum');
});
