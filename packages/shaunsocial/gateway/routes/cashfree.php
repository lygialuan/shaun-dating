<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Gateway\Http\Controllers\Web\CashfreeController;

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

Route::post('/gateway/cashfree/ipn', [CashfreeController::class, 'ipn'])->name('gateway.cashfree.ipn');
