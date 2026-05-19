<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Chat\Http\Controllers\Api\ChatController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('chat/get_request_count', [ChatController::class, 'get_request_count'])->name('chat_get_request_count');
    Route::get('chat/get_room/{page?}', [ChatController::class, 'get_room'])->where(['page' => '[0-9]+'])->name('chat_get_room');
    Route::get('chat/get_room_request/{page?}', [ChatController::class, 'get_room_request'])->where(['page' => '[0-9]+'])->name('chat_get_room_request');

    Route::get('chat/search_room/{text}', [ChatController::class, 'search_room'])->name('chat_search_room');
    Route::get('chat/search_room_request/{text}', [ChatController::class, 'search_room_request'])->name('chat_search_room_request');

    Route::get('chat/detail/{id}', [ChatController::class, 'detail'])->where(['id' => '[0-9]+'])->name('chat_detail');
    Route::get('chat/get_room_message/{id}/{page?}', [ChatController::class, 'get_room_message'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('chat_get_room_message');

    Route::post('chat/store_room_seen/{id}', [ChatController::class, 'store_room_seen'])->name('chat_store_room_seen');
    Route::post('chat/store_room_unseen/{id}', [ChatController::class, 'store_room_unseen'])->name('chat_store_room_unseen');
    Route::post('chat/store_room', [ChatController::class, 'store_room'])->name('chat_store_room');
    Route::post('chat/store_room_message', [ChatController::class, 'store_room_message'])->name('chat_store_room_message');
    Route::post('chat/store_room_status', [ChatController::class, 'store_room_status'])->name('chat_store_room_status');
    Route::post('chat/store_room_notify', [ChatController::class, 'store_room_notify'])->name('chat_store_room_notify');
    Route::post('chat/clear_room_message', [ChatController::class, 'clear_room_message'])->name('chat_clear_room_message');

    Route::post('chat/upload_photo', [ChatController::class, 'upload_photo'])->name('chat_upload_photo');
    Route::post('chat/delete_message_item', [ChatController::class, 'delete_message_item'])->name('chat_delete_message_item');    
    Route::post('chat/check_room_online', [ChatController::class, 'check_room_online'])->name('chat_check_room_online');

    Route::post('chat/store_active_room/{id}', [ChatController::class, 'store_active_room'])->where(['id' => '[0-9]+'])->name('chat_store_active_room');
    Route::post('chat/store_inactive_room/{id}', [ChatController::class, 'store_inactive_room'])->where(['id' => '[0-9]+'])->name('chat_store_inactive_room');

    Route::post('chat/unsent_room_message/{id}', [ChatController::class, 'unsent_room_message'])->where(['id' => '[0-9]+'])->name('unsent_room_message');
    Route::post('chat/upload_file', [ChatController::class, 'upload_file'])->name('chat_upload_file');
    Route::post('chat/send_fund', [ChatController::class, 'send_fund'])->name('chat_send_fund');
    Route::post('chat/delete_room', [ChatController::class, 'delete_room'])->name('chat_delete_room');

    Route::post('chat/store_audio', [ChatController::class, 'store_audio'])->name('chat_store_audio');
});