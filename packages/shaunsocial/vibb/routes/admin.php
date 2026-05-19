<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Vibb\Http\Controllers\Admin\SongController;

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
    Route::get('/vibb/songs', [SongController::class, 'index'])->name('vibb.song.index');
    Route::get('/vibb/songs/create/{id?}', [SongController::class, 'create'])->where('id', '[0-9]+')->name('vibb.song.create');
    Route::post('/vibb/songs/store', [SongController::class, 'store'])->name('vibb.song.store');    
    Route::get('/vibb/songs/delete/{id}', [SongController::class, 'delete'])->where('id', '[0-9]+')->name('vibb.song.delete');
});