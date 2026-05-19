<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\AiChatProfiles\Http\Controllers\Api\AiSuggestionController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('ai_chat_profiles/suggestion/{id}', [AiSuggestionController::class, 'suggestion'])->where(['id' => '[0-9]+'])->name('ai_chat_profiles.suggestion');
});