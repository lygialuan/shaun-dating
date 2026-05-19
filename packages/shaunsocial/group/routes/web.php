<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Group\Http\Controllers\Web\GroupController;

Route::group(['middleware' => ['web'], 'as' => 'web.'], function () {
    //profile url
    Route::get('/groups/{id}/{slug}', [GroupController::class, 'profile'])->where('id', '[0-9]+')->name('group.profile');
        
    Route::get('/groups/pending_post/{id}/{post_id}', function () {
        return view('shaun_core::app');
    })->where(['id' => '[0-9]+', 'post_id' => '[0-9]+'])->name('group.admin_pending_post');

    Route::get('/groups/{id}/user-manage', function () {
        return view('shaun_core::app');
    })->where(['id' => '[0-9]+'])->name('group.member_pending_post');

    Route::get('/groups/join_request/{id}/{user_id}', function () {
        return view('shaun_core::app');
    })->where(['id' => '[0-9]+', 'user_id' => '[0-9]+'])->name('group.member_request_join');

    Route::get('/lists/groups',function () {
        return view('shaun_core::app');
    })->name('group.list_group');

    Route::get('/groups',function () {
        return view('shaun_core::app');
    })->name('group.index');
});
