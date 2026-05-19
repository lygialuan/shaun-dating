<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    //vibb url
    Route::get('/clips', function () {
        return view('shaun_core::app');
    })->name('vibb.index');
});
