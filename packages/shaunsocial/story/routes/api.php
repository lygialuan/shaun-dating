<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Story\Http\Controllers\Api\StoryController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('story/config', [StoryController::class, 'config'])->name('story_config');
    Route::get('story/search_song/{text}', [StoryController::class, 'search_song'])->name('story_search_song');
    Route::get('story/get/{page?}', [StoryController::class, 'get'])->where(['page' => '[0-9]+'])->name('story_get');
    Route::get('story/detail/{id}', [StoryController::class, 'detail'])->where(['id' => '[0-9]+'])->name('story_detail')->withoutMiddleware('auth:sanctum');
    Route::post('story/store', [StoryController::class, 'store'])->name('story_store');
    Route::post('story/delete_item', [StoryController::class, 'delete_item'])->name('story_delete_item');
    Route::post('story/store_view_item', [StoryController::class, 'store_view_item'])->name('story_store_view_item');
    Route::get('story/get_view/{id}/{page?}', [StoryController::class, 'get_view'])->where(['id' => '[0-9]+', 'page' => '[0-9]+'])->name('story_get_view');
    Route::get('story/my/{page?}', [StoryController::class, 'my'])->where(['page' => '[0-9]+'])->name('story_my');
    Route::get('story/detail_item/{id}', [StoryController::class, 'detail_item'])->where(['id' => '[0-9]+'])->name('story_detail_item');
    Route::get('story/detail_in_list/{id}', [StoryController::class, 'detail_in_list'])->where(['id' => '[0-9]+'])->name('story_detail_in_list');

    Route::post('story/store_message', [StoryController::class, 'store_message'])->name('story_store_message');
    Route::post('story/share_message', [StoryController::class, 'share_message'])->name('story_share_message');
    Route::post('story/upload_video', [StoryController::class, 'upload_video'])->name('story_upload_video');
});
