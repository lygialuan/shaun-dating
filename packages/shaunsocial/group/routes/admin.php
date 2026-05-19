<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Group\Http\Controllers\Admin\GroupCategoryController;
use Packages\ShaunSocial\Group\Http\Controllers\Admin\GroupController;

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
Route::group(['prefix' => env('APP_ADMIN_PREFIX', 'admin'), 'as' => 'admin.', 'middleware' => ['web', 'is.admin']], function () {
    Route::get('/groups', [GroupController::class, 'index'])->name('group.index');
    Route::get('/groups/delete/{id}', [GroupController::class, 'delete'])->where('id', '[0-9]+')->name('group.delete');
    Route::get('/groups/disable/{id}', [GroupController::class, 'disable'])->where('id', '[0-9]+')->name('group.disable');
    Route::get('/groups/active/{id}', [GroupController::class, 'active'])->where('id', '[0-9]+')->name('group.active');
    Route::get('/groups/approve/{id}', [GroupController::class, 'approve'])->where('id', '[0-9]+')->name('group.approve');
    Route::get('/groups/admin_manage/{id}', [GroupController::class, 'admin_manage'])->where('id', '[0-9]+')->name('group.admin_manage');
    Route::post('/groups/store_manage', [GroupController::class, 'store_manage'])->name('group.store_manage');
    Route::get('/groups/popular/{id}', [GroupController::class, 'popular'])->where('id', '[0-9]+')->name('group.popular');
    Route::get('/groups/remove_popular/{id}', [GroupController::class, 'remove_popular'])->where('id', '[0-9]+')->name('group.remove_popular');

    Route::get('/groups/categories', [GroupCategoryController::class, 'index'])->name('group.category.index');
    Route::get('/groups/categories/create/{id?}', [GroupCategoryController::class, 'create'])->where('id', '[0-9]+')->name('group.category.create');
    Route::post('/groups/categories/store', [GroupCategoryController::class, 'store'])->name('group.category.store');    
    Route::get('/groups/categories/delete/{id}', [GroupCategoryController::class, 'delete'])->where('id', '[0-9]+')->name('group.category.delete');
    Route::post('/groups/categories/store_order', [GroupCategoryController::class, 'store_order'])->name('group.category.store_order');
});