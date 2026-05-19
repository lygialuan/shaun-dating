<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Dating\Http\Controllers\Admin\DatingAttributeController;
use Packages\ShaunSocial\Dating\Http\Controllers\Admin\DatingAttributeValueController;
use Packages\ShaunSocial\Dating\Http\Controllers\Admin\DatingInterestAttributeController;
use Packages\ShaunSocial\Dating\Http\Controllers\Admin\DatingInterestAttributeValueController;
use Packages\ShaunSocial\Dating\Http\Controllers\Admin\DatingProfileCompletionSettingController;
use Packages\ShaunSocial\Dating\Http\Controllers\Admin\DatingProfilePicturesController;

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
    Route::group(['prefix' => '/dating/attributes/'], function() {
        Route::get('', [DatingAttributeController::class, 'index'])->name('dating.attribute');
        Route::get('create/{id?}', [DatingAttributeController::class, 'create'])->where('id', '[0-9]+')->name('dating.attribute.create');
        Route::post('store', [DatingAttributeController::class, 'store'])->name('dating.attribute.store');
        Route::post('store_order', [DatingAttributeController::class, 'store_order'])->name('dating.attribute.store_order');
        Route::get('delete/{id}', [DatingAttributeController::class, 'delete'])->where('id', '[0-9]+')->name('dating.attribute.delete');
        Route::get('search',[DatingAttributeController::class, 'search'])->name('dating.search');
    });

    Route::group(['prefix' => '/dating/attribute/values'], function () {
        Route::get('{attribute_id}', [DatingAttributeValueController::class, 'index'])->where('attribute_id', '[0-9]+')->name('dating.attribute.value');
        Route::get('create/{attribute_id}/{id?}', [DatingAttributeValueController::class, 'create'])->where('attribute_id', '[0-9]+')->where('id', '[0-9]+')->name('dating.attribute.value.create');
        Route::post('store', [DatingAttributeValueController::class, 'store'])->name('dating.attribute.value.store');
        Route::get('delete', [DatingAttributeValueController::class, 'delete'])->name('dating.attribute.value.delete');
        Route::post('store_manage', [DatingAttributeValueController::class, 'store_manage'])->name('dating.attribute.value.store_manage');
    });

    Route::group(['prefix' => '/dating/interest_attributes/'], function() {
        Route::get('', [DatingInterestAttributeController::class, 'index'])->name('dating.interest_attribute');
        Route::get('create/{id?}', [DatingInterestAttributeController::class, 'create'])->where('id', '[0-9]+')->name('dating.interest_attribute.create');
        Route::post('store', [DatingInterestAttributeController::class, 'store'])->name('dating.interest_attribute.store');
        Route::post('store_order', [DatingInterestAttributeController::class, 'store_order'])->name('dating.interest_attribute.store_order');
        Route::get('delete/{id}', [DatingInterestAttributeController::class, 'delete'])->where('id', '[0-9]+')->name('dating.interest_attribute.delete');
        Route::get('search',[DatingInterestAttributeController::class, 'search'])->name('dating.search');
    });

    Route::group(['prefix' => '/dating/interest_attribute/values'], function () {
        Route::get('{attribute_id}', [DatingInterestAttributeValueController::class, 'index'])->where('attribute_id', '[0-9]+')->name('dating.interest_attribute.value');
        Route::get('create/{attribute_id}/{id?}', [DatingInterestAttributeValueController::class, 'create'])->where('attribute_id', '[0-9]+')->where('id', '[0-9]+')->name('dating.interest_attribute.value.create');
        Route::post('store', [DatingInterestAttributeValueController::class, 'store'])->name('dating.interest_attribute.value.store');
        Route::get('delete', [DatingInterestAttributeValueController::class, 'delete'])->name('dating.interest_attribute.value.delete');
        Route::post('store_manage', [DatingInterestAttributeValueController::class, 'store_manage'])->name('dating.interest_attribute.value.store_manage');
    });

    Route::group(['prefix' => '/dating/profile_completion_settings'], function () {
        Route::get('', [DatingProfileCompletionSettingController::class, 'index'])->name('dating.profile_completion_settings');
        Route::post('store', [DatingProfileCompletionSettingController::class, 'store'])->name('dating.profile_completion_settings.store');
    });
    
    Route::group(['prefix' => '/dating/profile_pictures/'], function() {
        Route::get('', [DatingProfilePicturesController::class, 'index'])->name('dating.profile_pictures');
        Route::get('view_detail/{id?}', [DatingProfilePicturesController::class, 'view_detail'])->where('id', '[0-9]+')->name('dating.profile_pictures.view_detail');
        Route::post('store', [DatingProfilePicturesController::class, 'store'])->name('dating.profile_pictures.store');
    });
}); 