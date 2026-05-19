<?php


use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/chat/inbox/{id}', function () {
        return view('shaun_core::app');
    })->name('chat.detail');

    Route::get('/chat/requests', function () {
        return view('shaun_core::app');
    })->name('chat.request');

    Route::get('/chat/requests/{id}', function () {
        return view('shaun_core::app');
    })->name('chat.request_detail');
});
