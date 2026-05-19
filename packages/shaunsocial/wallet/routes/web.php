<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/wallet/deposit/successful', function () {
        return view('shaun_core::app');
    })->name('wallet.deposit_successful');
    
    Route::get('/wallet/deposit/cancel', function () {
        return view('shaun_core::app');
    })->name('wallet.deposit_cancel');

    Route::get('/wallet', function () {
        return view('shaun_core::app');
    })->name('wallet.index');
});
