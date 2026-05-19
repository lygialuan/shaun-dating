<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\AiChatProfiles\Http\Controllers\Admin\AiPersonaConfigController;

Route::group(['prefix' => env('APP_ADMIN_PREFIX', 'admin'), 'as' => 'admin.', 'middleware' => ['web', 'is.admin']], function () {
    Route::get('/user_pages/{id}/ai-config', [AiPersonaConfigController::class, 'edit'])->where('id', '[0-9]+')->name('user_page.ai_config.edit');
    Route::post('/user_pages/{id}/ai-config', [AiPersonaConfigController::class, 'store'])->where('id', '[0-9]+')->name('user_page.ai_config.store');
});
