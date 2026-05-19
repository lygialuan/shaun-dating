<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Api\ReportController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\AppController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\AuthController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\BookmarkController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\CommentController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\CountryController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\FollowController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\HashtagController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\HistoryController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\InviteController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\LikeController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\NotificationController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\PageController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\PostController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\SearchController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\SubscriptionController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\UserController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\UtilityController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\ContentWarningController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\TwoFactorController;
use Packages\ShaunSocial\Core\Http\Controllers\Api\UserListController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {    
    Route::post('post/upload_photo', [PostController::class, 'upload_photo'])->name('post_upload_photo');
    Route::post('post/upload_video', [PostController::class, 'upload_video'])->name('post_upload_video');
    Route::post('post/store', [PostController::class, 'store'])->name('post_store');
    Route::get('post/get/{id}', [PostController::class, 'get'])->where(['id' => '[0-9]+'])->name('post_get')->withoutMiddleware('auth:sanctum');
    Route::get('post/profile/{id}/{page?}', [PostController::class, 'profile'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('post_profile')->withoutMiddleware('auth:sanctum');
    Route::get('post/profile_media/{id}/{page?}', [PostController::class, 'profile_media'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('post_profile_media')->withoutMiddleware('auth:sanctum');
    Route::get('post/home/{page?}', [PostController::class, 'home'])->where(['page' => '[0-9]+'])->name('post_home');
    Route::post('post/delete', [PostController::class, 'delete'])->name('post_delete');
    Route::post('post/fetch_link', [PostController::class, 'fetch_link'])->name('post_fetch_link');
    Route::post('post/delete_item', [PostController::class, 'delete_item'])->name('post_delete_item');
    Route::get('post/hashtag/{hashtag}/{page?}', [PostController::class, 'hashtag'])->where(['page' => '[0-9]+'])->name('post_hashtag');
    Route::get('post/discovery/{page?}', [PostController::class, 'discovery'])->where(['page' => '[0-9]+'])->name('post_discovery')->withoutMiddleware('auth:sanctum');
    Route::get('post/watch/{page?}', [PostController::class, 'watch'])->where(['page' => '[0-9]+'])->name('post_watch')->withoutMiddleware('auth:sanctum');
    Route::get('post/media/{page?}', [PostController::class, 'media'])->where(['page' => '[0-9]+'])->name('post_media')->withoutMiddleware('auth:sanctum');
    Route::get('post/document/{page?}', [PostController::class, 'document'])->where(['page' => '[0-9]+'])->name('post_document')->withoutMiddleware('auth:sanctum');
    Route::post('post/store_edit', [PostController::class, 'store_edit'])->name('post_store_edit');
    Route::post('post/upload_file', [PostController::class, 'upload_file'])->name('post_upload_file');
    Route::post('post/store_vote_poll', [PostController::class, 'store_vote_poll'])->name('post_store_vote_poll');
    Route::get('post/get_poll_item_vote/{poll_item_id}/{page?}', [PostController::class, 'get_poll_item_vote'])->where(['poll_item_id' => '[0-9]+', 'page' => '[0-9]+'])->name('post_get_poll_item_vote')->withoutMiddleware('auth:sanctum');
    Route::post('post/store_comment_privacy', [PostController::class, 'store_comment_privacy'])->name('post_store_comment_privacy');
    Route::post('post/store_content_warning', [PostController::class, 'store_content_warning'])->name('post_store_content_warning');
    Route::post('post/upload_thumb', [PostController::class, 'upload_thumb'])->name('post_upload_thumb');
    Route::post('post/get_by_ids', [PostController::class, 'get_by_ids'])->name('post_get_by_ids');
    Route::get('post/get_new_home/{id?}', [PostController::class, 'get_new_home'])->where(['id' => '[0-9]+'])->name('get_new_home');

    Route::post('like/store', [LikeController::class, 'store'])->name('like_store');
    Route::get('like/get/{subject_type}/{subject_id}/{page?}', [LikeController::class, 'get'])->where(['subject_id' => '[0-9]+', 'page' => '[0-9]+'])->name('like_get')->withoutMiddleware('auth:sanctum');

    Route::post('comment/store', [CommentController::class, 'store'])->name('comment_store');
    Route::post('comment/store_reply', [CommentController::class, 'store_reply'])->name('comment_store_reply');
    Route::get('comment/get/{subject_type}/{subject_id}/{page?}', [CommentController::class, 'get'])->where(['subject_id' => '[0-9]+', 'page' => '[0-9]+'])->name('comment_get')->withoutMiddleware('auth:sanctum');
    Route::get('comment/get_reply/{id}/{page?}', [CommentController::class, 'get_reply'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('comment_get_reply')->withoutMiddleware('auth:sanctum');
    Route::post('comment/delete', [CommentController::class, 'delete'])->name('comment_delete');
    Route::post('comment/delete_reply', [CommentController::class, 'delete_reply'])->name('comment_delete_reply');
    Route::get('comment/get_single/{subject_type}/{subject_id}/{comment_id}/{reply_id?}', [CommentController::class, 'get_single'])->where(['subject_id' => '[0-9]+', 'comment_id' => '[0-9]+', 'reply_id' => '[0-9]+'])->name('comment_get_singer')->withoutMiddleware('auth:sanctum');
    Route::post('comment/store_edit', [CommentController::class, 'store_edit'])->name('comment_store_edit');
    Route::post('comment/store_reply_edit', [CommentController::class, 'store_reply_edit'])->name('comment_store_reply_edit');
    Route::post('comment/upload_photo', [CommentController::class, 'upload_photo'])->name('comment_upload_photo');
    Route::post('comment/delete_item', [CommentController::class, 'delete_item'])->name('comment_delete_item');
    Route::post('comment/upload_reply_photo', [CommentController::class, 'upload_reply_photo'])->name('comment_upload_reply_photo');
    Route::post('comment/delete_reply_item', [CommentController::class, 'delete_reply_item'])->name('comment_delete_reply_item');

    Route::post('follow/user/store', [FollowController::class, 'user_store'])->name('follow_user_store');
    Route::get('follow/user/get_follower/{id}/{page?}', [FollowController::class, 'user_get_follower'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('follow_user_get_follower')->withoutMiddleware('auth:sanctum');
    Route::get('follow/user/get_following/{id}/{page?}', [FollowController::class, 'user_get_following'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('follow_user_get_following')->withoutMiddleware('auth:sanctum');
    Route::post('follow/hashtag/store', [FollowController::class, 'hashtag_store'])->name('follow_hashtag_store');
    Route::get('follow/hashtag/{page?}', [FollowController::class, 'hashtag'])->where(['page' => '[0-9]+'])->name('follow_hashtag');
    Route::post('follow/user/store_notification', [FollowController::class, 'user_store_notification'])->name('follow_user_store_notification');
    Route::get('follow/user/get_my_follower/{type?}/{page?}', [FollowController::class, 'user_get_my_follower'])->where(['page' => '[0-9]+'])->name('follow_user_get_my_follower');
    Route::get('follow/user/get_my_following/{type?}/{page?}', [FollowController::class, 'user_get_my_following'])->where(['page' => '[0-9]+'])->name('follow_user_get_my_following');

    Route::get('hashtag/get/{hashtag}', [HashtagController::class, 'get'])->name('hashtag_get')->withoutMiddleware('auth:sanctum');;
    Route::get('hashtag/search/{text}', [HashtagController::class, 'search'])->name('hashtag_search');
    Route::get('hashtag/suggest_search', [HashtagController::class, 'suggest_search'])->where(['page' => '[0-9]+'])->name('hashtag_suggest_search');
    Route::get('hashtag/suggest', [HashtagController::class, 'suggest'])->name('hashtag_suggest');
    Route::get('hashtag/suggest_signup/{text?}', [HashtagController::class, 'suggest_signup'])->name('hashtag_suggest_signup');
    Route::get('hashtag/trending', [HashtagController::class, 'trending'])->name('hashtag_trending');
    Route::get('hashtag/trending_search', [HashtagController::class, 'trending_search'])->name('hashtag_trending_search');

    Route::get('user/me', [UserController::class, 'me'])->name('user_me')->withoutMiddleware('auth:sanctum');
    Route::get('user/search/{text}', [UserController::class, 'search'])->name('user_search');
    Route::get('user/suggest', [UserController::class, 'suggest'])->name('user_suggest');
    Route::get('user/suggest_signup/{text?}', [UserController::class, 'suggest_signup'])->name('user_suggest_signup');
    Route::get('user/trending', [UserController::class, 'trending'])->name('user_trending');
    Route::get('user/profile/{user_name}', [UserController::class, 'profile'])->name('user_profile')->withoutMiddleware('auth:sanctum');
    Route::post('user/store_block', [UserController::class, 'store_block'])->name('user_store_block');
    Route::get('user/block/{type?}/{page?}', [UserController::class, 'block'])->where(['page' => '[0-9]+'])->name('user_block');
    Route::get('user/notification_setting', [UserController::class, 'notification_setting'])->name('user_notification_setting');
    Route::post('user/store_notification_setting', [UserController::class, 'store_notification_setting'])->name('user_store_notification_setting');
    Route::get('user/privacy_setting', [UserController::class, 'privacy_setting'])->name('user_privacy_setting');
    Route::post('user/store_privacy_setting', [UserController::class, 'store_privacy_setting'])->name('user_store_privacy_setting');
    Route::get('user/email_setting', [UserController::class, 'email_setting'])->name('user_email_setting');
    Route::post('user/store_email_setting', [UserController::class, 'store_email_setting'])->name('user_store_email_setting');
    Route::get('user/ping', [UserController::class, 'ping'])->name('user_ping');
    Route::post('user/store_login_first', [UserController::class, 'store_login_first'])->name('user_store_login_first');
    Route::post('user/store_darkmode', [UserController::class, 'store_darkmode'])->name('user_store_darkmode');
    Route::post('user/store_video_auto_play', [UserController::class, 'store_video_auto_play'])->name('user_store_video_auto_play');
    Route::get('user/suggest_search', [UserController::class, 'suggest_search'])->name('user_suggest_search');
    Route::get('user/trending_search', [UserController::class, 'trending_search'])->name('user_trending_search');
    Route::post('user/upload_cover', [UserController::class, 'upload_cover'])->name('user_upload_cover');
    Route::post('user/upload_avatar', [UserController::class, 'upload_avatar'])->name('user_upload_avatar');
    Route::get('user/get_edit_profile', [UserController::class, 'get_edit_profile'])->name('user_get_edit_profile');
    Route::post('user/store_edit_profile', [UserController::class, 'store_edit_profile'])->name('user_store_edit_profile');
    Route::post('user/send_email_verify', [UserController::class, 'send_email_verify'])->middleware('throttle:send_email')->name('user_send_email_verify');
    Route::post('user/check_email_verify', [UserController::class, 'check_email_verify'])->name('user_check_email_verify');
    Route::post('user/change_password', [UserController::class, 'change_password'])->name('user_change_password');
    Route::post('user/send_code_forgot_password', [UserController::class, 'send_code_forgot_password'])->middleware('throttle:send_email')->name('user_send_code_forgot_password')->withoutMiddleware('auth:sanctum');
    Route::post('user/check_code_forgot_password', [UserController::class, 'check_code_forgot_password'])->name('user_check_code_forgot_password')->withoutMiddleware('auth:sanctum');
    Route::post('user/store_forgot_password', [UserController::class, 'store_forgot_password'])->name('user_store_forgot_password')->withoutMiddleware('auth:sanctum');
    Route::post('user/check_password', [UserController::class, 'check_password'])->name('user_check_password');
    Route::post('user/store_account', [UserController::class, 'store_account'])->name('user_store_account');
    Route::post('user/delete', [UserController::class, 'delete'])->name('user_delete');
    Route::post('user/store_language', [UserController::class, 'store_language'])->name('user_store_language');
    Route::get('user/get_download', [UserController::class, 'get_download'])->name('user_get_download');
    Route::post('user/store_download', [UserController::class, 'store_download'])->name('user_store_download');
    Route::post('user/send_add_email_password_verify', [UserController::class, 'send_add_email_password_verify'])->middleware('throttle:send_email')->name('user_send_add_email_password_verify');
    Route::post('user/store_add_email_password_verify', [UserController::class, 'store_add_email_password_verify'])->name('user_store_add_email_password_verify');
    Route::post('user/remove_login_other_device', [UserController::class, 'remove_login_other_device'])->name('user_remove_login_other_device');
    Route::post('user/upload_photos_verify', [UserController::class, 'upload_photos_verify'])->name('user_upload_photos_verify');
    Route::post('user/remove_photo_verify', [UserController::class, 'remove_photo_verify'])->name('user_remove_photo_verify');
    Route::post('user/change_main_photo', [UserController::class, 'change_main_photo'])->name('user_change_main_photo');
    Route::post('user/completed_photo_verify', [UserController::class, 'completed_photo_verify'])->name('user_completed_photo_verify');
    Route::get('user/get_all_users', [UserController::class, 'get_all_users'])->name('user_get_all_users');

    Route::post('bookmark/store', [BookmarkController::class, 'store'])->name('bookmark_store');
    Route::get('bookmark/get/{subject_type}/{page?}', [BookmarkController::class, 'get'])->where('page', '[0-9]+')->name('bookmark_get');

    Route::get('report/category', [ReportController::class, 'category'])->name('report_category')->withoutMiddleware('auth:sanctum');
    Route::post('report/store', [ReportController::class, 'store'])->name('report_store');

    Route::get('invite/info', [InviteController::class, 'info'])->name('invite_info');
    Route::get('invite/get', [InviteController::class, 'get'])->name('invite_get');
    Route::post('invite/store', [InviteController::class, 'store'])->name('invite_store');
    Route::post('invite/store_csv', [InviteController::class, 'store_csv'])->name('invite_store_csv');
    
    Route::get('search/suggest', [SearchController::class, 'suggest'])->name('search_suggest')->withoutMiddleware('auth:sanctum');
    Route::get('search/text', [SearchController::class, 'text'])->name('search_text')->withoutMiddleware('auth:sanctum');;
    Route::get('search/hashtag', [SearchController::class, 'hashtag'])->name('search_hashtag')->withoutMiddleware('auth:sanctum');
    Route::get('search/get_search_histories', [SearchController::class, 'get_search_histories'])->name('search_get_search_histories');
    Route::post('search/store_search_history', [SearchController::class, 'store_search_history']) ->name('search_store_search_history');
    Route::post('search/delete_search_history', [SearchController::class, 'delete_search_history'])->name('search_delete_search_history');

    Route::get('notification/get/{page?}', [NotificationController::class, 'get'])->where(['page' => '[0-9]+'])->name('notification_get');
    Route::post('notification/store_enable', [NotificationController::class, 'store_enable'])->name('notification_store_enable');
    Route::post('notification/store_seen', [NotificationController::class, 'store_seen'])->name('notification_store_seen');
    Route::post('notification/mark_all_as_read', [NotificationController::class, 'mark_all_as_read'])->name('notification_mark_all_as_read');

    Route::post('utility/share_email', [UtilityController::class, 'share_email'])->name('utility_share_email');
    Route::post('utility/unsubscribe_email', [UtilityController::class, 'unsubscribe_email'])->name('utility_unsubscribe_email')->withoutMiddleware('auth:sanctum');
    Route::post('utility/check_access_code', [UtilityController::class, 'check_access_code'])->name('utility_check_access_code')->withoutMiddleware('auth:sanctum');
    Route::post('utility/store_contact', [UtilityController::class, 'store_contact'])->name('utility_store_contact')->withoutMiddleware('auth:sanctum');
    Route::post('utility/store_fcm_token', [UtilityController::class, 'store_fcm_token'])->name('utility_store_fcm_token');
    Route::post('utility/remove_web_fcm_token', [UtilityController::class, 'remove_web_fcm_token'])->name('utility_remove_web_fcm_token');
    Route::post('utility/content_translate', [UtilityController::class, 'content_translate'])->name('utility_content_translate');

    Route::get('history/get/{subject_type}/{subject_id}/{page?}', [HistoryController::class, 'get'])->where(['page' => '[0-9]+'])->name('history_get')->withoutMiddleware('auth:sanctum');
    
    Route::get('page/get/{slug}', [PageController::class, 'get'])->name('page_get')->withoutMiddleware('auth:sanctum');
    Route::get('app/get_mobile_menu', [AppController::class, 'get_mobile_menu'])->name('app_get_mobile_menu');

    Route::get('subscription/config', [SubscriptionController::class, 'config'])->name('subscription_config');
    Route::get('subscription/get', [SubscriptionController::class, 'get'])->name('subscription_get');
    Route::get('subscription/get_detail/{id}', [SubscriptionController::class, 'get_detail'])->where(['id' => '[0-9]+'])->name('subscription_get_detail');
    Route::post('subscription/store_cancel', [SubscriptionController::class, 'store_cancel'])->name('subscription_store_cancel');
    Route::post('subscription/store_resume', [SubscriptionController::class, 'store_resume'])->name('subscription_store_resume');
    Route::get('subscription/get_transaction/{id}/{page?}', [SubscriptionController::class, 'get_transaction'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('subscription_get_transaction');

    Route::get('country/get', [CountryController::class, 'get'])->name('country_get')->withoutMiddleware('auth:sanctum');
    Route::get('country/get_state/{country_id}', [CountryController::class, 'get_state'])->where(['country_id' => '[0-9]+'])->name('country_get_state')->withoutMiddleware('auth:sanctum');
    Route::get('country/get_city/{state_id}', [CountryController::class, 'get_city'])->where(['state_id' => '[0-9]+'])->name('country_get_city')->withoutMiddleware('auth:sanctum');

    Route::get('content_warning/category', [ContentWarningController::class, 'category'])->name('content_warning_category')->withoutMiddleware('auth:sanctum');

    Route::post('user/send_phone_verify', [UserController::class, 'send_phone_verify'])->name('user_send_phone_verify');
    Route::post('user/check_phone_verify', [UserController::class, 'check_phone_verify'])->name('user_check_phone_verify');
    Route::post('user/change_phone_verify', [UserController::class, 'change_phone_verify'])->name('user_change_phone_verify');
    Route::post('user/change_phone_when_edit', [UserController::class, 'change_phone_when_edit'])->name('user_change_phone_when_edit');
    Route::post('user/check_phone_when_edit', [UserController::class, 'check_phone_when_edit'])->name('user_check_phone_when_edit');

    Route::post('post/store_stop_comment', [PostController::class, 'store_stop_comment'])->name('post_store_stop_comment');

    Route::post('post/store_pin_home', [PostController::class, 'store_pin_home'])->name('post_store_pin_home');
    Route::post('post/store_pin_profile', [PostController::class, 'store_pin_profile'])->name('post_store_pin_profile');

    Route::get('user_list/get_count', [UserListController::class, 'get_count'])->name('user_list_get_count');
    Route::get('user_list/get/{page?}', [UserListController::class, 'get'])->where(['page' => '[0-9]+'])->name('user_list_get');
    Route::post('user_list/store', [UserListController::class, 'store'])->name('user_list_store');
    Route::post('user_list/delete', [UserListController::class, 'delete'])->name('user_list_delete');
    Route::get('user_list/get_members', [UserListController::class, 'get_members'])->name('user_list_get_members');
    Route::post('user_list/store_members', [UserListController::class, 'store_members'])->name('user_list_store_members');
    Route::post('user_list/delete_member', [UserListController::class, 'delete_member'])->name('user_list_delete_member');
    Route::get('user_list/send_message_config', [UserListController::class, 'send_message_config'])->name('user_list_send_message_config');
    Route::post('user_list/send_message', [UserListController::class, 'send_message'])->name('user_list_send_message');
    Route::get('user_list/search_for_send', [UserListController::class, 'search_for_send'])->name('user_list_search_for_send');

    Route::get('two_factor/get_current', [TwoFactorController::class, 'get_current'])->name('two_factor_get_current');
    Route::post('two_factor/remove', [TwoFactorController::class, 'remove'])->name('two_factor_remove');
    Route::post('two_factor/send_setup_email', [TwoFactorController::class, 'send_setup_email'])->name('two_factor_send_setup_email');
    Route::post('two_factor/verify_setup_email', [TwoFactorController::class, 'verify_setup_email'])->name('two_factor_verify_setup_email');
    Route::post('two_factor/send_setup_phone', [TwoFactorController::class, 'send_setup_phone'])->name('two_factor_send_setup_phone');
    Route::post('two_factor/verify_setup_phone', [TwoFactorController::class, 'verify_setup_phone'])->name('two_factor_verify_setup_phone');
    Route::post('two_factor/get_code_app', [TwoFactorController::class, 'get_code_app'])->name('two_factor_get_code_app');
    Route::post('two_factor/verify_code_app', [TwoFactorController::class, 'verify_code_app'])->name('two_factor_verify_code_app');

    Route::post('two_factor/get_login_current', [TwoFactorController::class, 'get_login_current'])->name('two_factor_get_login_current')->withoutMiddleware('auth:sanctum');
    Route::post('two_factor/send_login_code', [TwoFactorController::class, 'send_login_code'])->name('two_factor_send_login_code')->withoutMiddleware('auth:sanctum');
    Route::post('two_factor/verify_login_code', [TwoFactorController::class, 'verify_login_code'])->name('two_factor_verify_login_code')->withoutMiddleware('auth:sanctum');
});

Route::group(['middleware' => 'api', 'prefix' => 'api'], function () {
    Route::get('init', [AppController::class, 'init'])->name('app_init');
    Route::get('csrf', [AppController::class, 'csrf'])->name('app_csrf');

    Route::post('layout/get-detail', [AppController::class, 'layout'])->name('app_layout');
});

Route::group(['middleware' => 'web', 'prefix' => 'api'], function () {
    Route::get('csrf', [AppController::class, 'csrf'])->name('app_csrf');
});

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('auth/logout', [AuthController::class, 'logout'])->name('auth_logout');
    Route::post('auth/login', [AuthController::class, 'login'])->name('auth_login')->middleware('throttle:login')->withoutMiddleware('auth:sanctum');
    Route::post('auth/signup', [AuthController::class, 'signup'])->name('auth_signup')->withoutMiddleware('auth:sanctum');
    Route::get('auth/signup/config', [AuthController::class, 'config'])->name('auth_config')->withoutMiddleware('auth:sanctum');

    Route::post('auth/login_with_code', [AuthController::class, 'login_with_code'])->name('login_with_code')->withoutMiddleware('auth:sanctum');
});