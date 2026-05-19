<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/pages', function () {
        return view('shaun_core::app');
    })->name('user_page.index');
});
