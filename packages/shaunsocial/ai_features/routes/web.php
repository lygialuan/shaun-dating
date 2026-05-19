<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/compliance/notice/{task}', function () {
        return view('shaun_core::app');
    })->where(['task' => '[0-9]+'])->name('compliance.notice');
});
