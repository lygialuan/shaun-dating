<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\MigrateOldDating\Http\Controllers\Admin\MigrateOldDatingController;


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
    Route::get('/migrate_old_dating/create', [MigrateOldDatingController::class, 'create'])->name('migrate_old_dating.create');
    Route::post('/migrate_old_dating/store', [MigrateOldDatingController::class, 'store'])->name('migrate_old_dating.store');
    Route::post('/migrate_old_dating/import', [MigrateOldDatingController::class, 'import'])->name('migrate_old_dating.import');
    Route::post('/migrate_old_dating/remove', [MigrateOldDatingController::class, 'remove'])->name('migrate_old_dating.remove');
});
