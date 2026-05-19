<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Vibb\Http\Controllers\Api\VibbController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('vibb/config', [VibbController::class, 'config'])->name('vibb_config');
    Route::get('vibb/search_song/{text}', [VibbController::class, 'search_song'])->name('vibb_search_song');
    Route::post('vibb/upload_video', [VibbController::class, 'upload_video'])->name('vibb_upload_video');
    Route::post('vibb/store', [VibbController::class, 'store'])->name('vibb_store');
    Route::get('vibb/for_you/{page?}', [VibbController::class, 'for_you'])->where(['page' => '[0-9]+'])->name('vibb_for_your')->withoutMiddleware('auth:sanctum');
    Route::get('vibb/following/{page?}', [VibbController::class, 'following'])->where(['page' => '[0-9]+'])->name('vibb_following');
    Route::get('vibb/profile/{id}/{page?}', [VibbController::class, 'profile'])->where(['page' => '[0-9]+', 'id' => '[0-9]+'])->name('vibb_profile')->withoutMiddleware('auth:sanctum');
    Route::get('vibb/my/{page?}', [VibbController::class, 'my'])->where(['page' => '[0-9]+'])->name('vibb_my');
});
