<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Chat\Http\Controllers\Admin\ChatController;

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
    if (env('ADMIN_SHOW_MANAGE_MESSAGE')) {
        Route::get('/chats', [ChatController::class, 'index'])->name('chat.index');
    }
    Route::get('/chats/detail/{id}', [ChatController::class, 'detail'])->where(['id' => '[0-9]+'])->name('chat.detail');
});