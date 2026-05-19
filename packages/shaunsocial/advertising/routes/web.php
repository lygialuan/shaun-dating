<?php


use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/advertising', function () {
        return view('shaun_core::app');
    })->name('advertising.index');
});
