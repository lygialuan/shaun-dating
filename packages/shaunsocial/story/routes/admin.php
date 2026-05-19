<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Story\Http\Controllers\Admin\BackgroundController;
use Packages\ShaunSocial\Story\Http\Controllers\Admin\SongController;

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
    Route::get('/stories/backgrounds', [BackgroundController::class, 'index'])->name('story.background.index');
    Route::get('/stories/backgrounds/create', [BackgroundController::class, 'create'])->name('story.background.create');
    Route::post('/stories/backgrounds/store', [BackgroundController::class, 'store'])->name('story.background.store');
    Route::post('/stories/backgrounds/store_active', [BackgroundController::class, 'store_active'])->name('story.background.store_active');
    Route::get('/stories/backgrounds/delete/{id}', [BackgroundController::class, 'delete'])->where('id', '[0-9]+')->name('story.background.delete');
    Route::post('/stories/backgrounds/store_order', [BackgroundController::class, 'store_order'])->name('story.background.store_order');

    Route::get('/stories/songs', [SongController::class, 'index'])->name('story.song.index');
    Route::get('/stories/songs/create/{id?}', [SongController::class, 'create'])->where('id', '[0-9]+')->name('story.song.create');
    Route::post('/stories/songs/store', [SongController::class, 'store'])->name('story.song.store');    
    Route::get('/stories/songs/delete/{id}', [SongController::class, 'delete'])->where('id', '[0-9]+')->name('story.song.delete');
});