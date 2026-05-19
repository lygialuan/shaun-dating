<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Chatbot\Http\Controllers\Api\ChatbotController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::post('/chatbot/send_message', [ChatbotController::class, 'send_message'])->name('chatbot_send_message');
    Route::get('/chatbot/get_history/{page?}', [ChatbotController::class, 'get_history'])->where(['page' => '[0-9]+'])->name('chatbot_get_history');
    Route::get('/chatbot/get_provider', [ChatbotController::class, 'get_provider'])->name('chatbot_get_provider');
    Route::post('/chatbot/clear_history', [ChatbotController::class, 'clear_history'])->name('chatbot_clear_history');
});
