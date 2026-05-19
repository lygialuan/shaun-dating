<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Dating\Http\Controllers\Api\DatingController;

Route::group(['prefix' => 'api/dating/'], function () {
    Route::group(['middleware' => ['auth:sanctum', 'api']], function () {
        Route::get('attributes', [DatingController::class, 'get_attributes'])->name('dating.attributes');
        Route::post('save_attributes', [DatingController::class, 'save_attributes'])->name('dating.save_attributes');
        Route::get('interest_attributes', [DatingController::class, 'get_interest_attributes'])->name('dating.interest_attributes');
        Route::post('save_interest_attributes', [DatingController::class, 'save_interest_attributes'])->name('dating.save_interest_attributes');
        Route::post('save_filter', [DatingController::class, 'save_filter'])->name('dating.save_filter');
        Route::get('suggestion_locations/{text?}', [DatingController::class, 'suggestion_locations'])->name('dating.suggestion_locations');
        Route::post('swipe', [DatingController::class, 'swipe'])->name('dating.swipe');
        Route::get('get_user_actions/{page?}/{action?}', [DatingController::class, 'get_user_actions'])->where(['page' => '[0-9]+'])->name('dating.get_user_actions');
    }); 
});