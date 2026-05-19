<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\AiFeatures\Http\Controllers\Admin\AiFeatureTaskController;

Route::group([
    'prefix' => env('APP_ADMIN_PREFIX', 'admin'),
    'as' => 'admin.',
    'middleware' => ['web', 'is.admin'],
], function () {
    Route::get('/ai-feature/tasks', [AiFeatureTaskController::class, 'index'])
        ->name('ai_feature.tasks.index');

    Route::get('/ai-feature/tasks/{task}', [AiFeatureTaskController::class, 'detail'])
        ->whereNumber('task')
        ->name('ai_feature.tasks.show');

    Route::post('/ai-feature/tasks/{task}/retry', [AiFeatureTaskController::class, 'retry'])
        ->whereNumber('task')
        ->name('ai_feature.tasks.retry');

    Route::delete('/ai-feature/tasks/{task}', [AiFeatureTaskController::class, 'destroy'])
        ->whereNumber('task')
        ->name('ai_feature.tasks.destroy');

    Route::delete('/ai-feature/tasks', [AiFeatureTaskController::class, 'destroyMultiple'])
        ->name('ai_feature.tasks.destroy_multiple');
});
