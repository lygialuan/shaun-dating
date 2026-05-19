<?php


use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    Route::get('/story/item/{id}', function () {
        return view('shaun_core::app');
    })->name('story.detail_item');
});
