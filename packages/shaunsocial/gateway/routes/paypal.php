<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Gateway\Http\Controllers\Web\PaypalController;

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

Route::post('/gateway/paypal/ipn', [PaypalController::class, 'ipn'])->name('gateway.paypal.ipn');