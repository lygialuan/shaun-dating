<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\UserPage\Http\Controllers\Admin\UserPageController;
use Packages\ShaunSocial\UserPage\Http\Controllers\Admin\UserPageCategoryController;
use Packages\ShaunSocial\UserPage\Http\Controllers\Admin\UserPageFeaturePackageController;
use Packages\ShaunSocial\UserPage\Http\Controllers\Admin\UserPageVerifyController;
use Packages\ShaunSocial\UserPage\Http\Controllers\Admin\UserPageCreateSubProfileController;
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
    Route::get('/user_pages', [UserPageController::class, 'index'])->name('user_page.index');
    Route::get('/user_pages/remove_login_all_devices/{id}', [UserPageController::class, 'remove_login_all_devices'])->where('id', '[0-9]+')->name('user_page.remove_login_all_devices');
    Route::get('/user_pages/edit/{id}', [UserPageController::class, 'edit'])->where('id', '[0-9]+')->name('user_page.edit');
    Route::get('/user_pages/admin_manage/{id}', [UserPageController::class, 'admin_manage'])->where('id', '[0-9]+')->name('user_page.admin_manage');
    Route::post('/user_pages/store_edit', [UserPageController::class, 'store_edit'])->name('user_page.store_edit');
    Route::post('/user_pages/store_manage', [UserPageController::class, 'store_manage'])->name('user_page.store_manage');

    Route::get('/user_pages/categories', [UserPageCategoryController::class, 'index'])->name('user_page.category.index');
    Route::get('/user_pages/categories/create/{id?}', [UserPageCategoryController::class, 'create'])->where('id', '[0-9]+')->name('user_page.category.create');
    Route::post('/user_pages/categories/store', [UserPageCategoryController::class, 'store'])->name('user_page.category.store');    
    Route::get('/user_pages/categories/delete/{id}', [UserPageCategoryController::class, 'delete'])->where('id', '[0-9]+')->name('user_page.category.delete');
    Route::post('/user_pages/categories/store_order', [UserPageCategoryController::class, 'store_order'])->name('user_page.category.store_order');

    Route::get('/user_pages/verifies', [UserPageVerifyController::class, 'index'])->name('user_page.verify.index');
    Route::get('/user_pages/verifies/store_verify/{id}', [UserPageVerifyController::class, 'store_verify'])->where('id', '[0-9]+')->name('user_page.verify.store_verify');
    Route::get('/user_pages/verifies/store_unverify/{id}', [UserPageVerifyController::class, 'store_unverify'])->where('id', '[0-9]+')->name('user_page.verify.store_unverify');
    Route::get('/user_pages/verifies/reject/{id}', [UserPageVerifyController::class, 'reject'])->where('id', '[0-9]+')->name('user_page.verify.reject');
    Route::post('/user_pages/verifies/store_reject', [UserPageVerifyController::class, 'store_reject'])->name('user_page.verify.store_reject');

    Route::get('/user_pages/feature_packages', [UserPageFeaturePackageController::class, 'index'])->name('user_page.feature_package.index');
    Route::get('/user_pages/feature_packages/create/{id?}', [UserPageFeaturePackageController::class, 'create'])->where('id', '[0-9]+')->name('user_page.feature_package.create');
    Route::post('/user_pages/feature_packages/store', [UserPageFeaturePackageController::class, 'store'])->name('user_page.feature_package.store');    
    Route::get('/user_pages/feature_packages/delete/{id}', [UserPageFeaturePackageController::class, 'delete'])->where('id', '[0-9]+')->name('user_page.feature_package.delete');
    Route::post('/user_pages/feature_packages/store_order', [UserPageFeaturePackageController::class, 'store_order'])->name('user_page.feature_package.store_order');

    Route::get('/user_pages/create_sub_profile', [UserPageCreateSubProfileController::class, 'index'])->name('user_page.create_sub_profile.index');
    Route::post('/user_pages/create_sub_profile/store', [UserPageCreateSubProfileController::class, 'store'])->name('user_page.create_sub_profile.store');
    Route::get('/user_pages/create_sub_profile/interest', [UserPageCreateSubProfileController::class, 'interest'])->name('user_page.create_sub_profile.interest');
    Route::get('/user_pages/create_sub_profile/more_about_me', [UserPageCreateSubProfileController::class, 'more_about_me'])->name('user_page.create_sub_profile.more_about_me');
    Route::post('/user_pages/create_sub_profile/state', [UserPageCreateSubProfileController::class, 'state'])->name('user_page.create_sub_profile.state');
    Route::post('/user_pages/create_sub_profile/city', [UserPageCreateSubProfileController::class, 'city'])->name('user_page.create_sub_profile.city');
    Route::get('/user_pages/create_sub_profile/upload_photos', [UserPageCreateSubProfileController::class, 'upload_photos'])->name('user_page.create_sub_profile.upload_photos');
    Route::post('/user_pages/create_sub_profile/store_upload_photos', [UserPageCreateSubProfileController::class, 'store_upload_photos'])->name('user_page.create_sub_profile.store_upload_photos');
});