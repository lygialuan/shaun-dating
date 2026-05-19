<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\GatewayRecurring\Http\Controllers\Web\CCBillController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Here is where you can register admin routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "admin" middleware group. Now create something great!
|
*/

Route::post('/gateway_recurring/ccbill/ipn', [CCBillController::class, 'ipn'])->name('gateway_recurring.ccbill.ipn');
