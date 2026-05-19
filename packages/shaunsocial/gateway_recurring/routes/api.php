<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\GatewayRecurring\Http\Controllers\Api\GatewayRecurringController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {    
    Route::post('gateway_recurring/create_link_payment', [GatewayRecurringController::class, 'create_link_payment'])->name('gateway_recurring_create_link_payment');
});
