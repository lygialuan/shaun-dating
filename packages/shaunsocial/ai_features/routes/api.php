<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\AiFeatures\Http\Controllers\Api\ComplianceController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('compliance/tasks/{task}', [ComplianceController::class, 'view'])
        ->where(['task' => '[0-9]+'])
        ->name('api.compliance.tasks.view');
});
