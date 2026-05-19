<?php


use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Core\Http\Controllers\Install\AdminController;
use Packages\ShaunSocial\Core\Http\Controllers\Install\DatabaseController;
use Packages\ShaunSocial\Core\Http\Controllers\Install\EnvironmentController;
use Packages\ShaunSocial\Core\Http\Controllers\Install\FinalController;
use Packages\ShaunSocial\Core\Http\Controllers\Install\RequirementsController;
use Packages\ShaunSocial\Core\Http\Controllers\Install\SettingController;
use Packages\ShaunSocial\Core\Http\Controllers\Install\UpdateController;

Route::group(['prefix' => 'install', 'as' => 'install.', 'middleware' => ['web', 'install']], function () {
    Route::get('environment/wizard', [EnvironmentController::class, 'environmentWizard'])->name('environmentWizard');
    Route::post('environment/validate', [EnvironmentController::class, 'validate'])->name('environmentValidate');
    Route::post('environment/saveWizard', [EnvironmentController::class, 'saveWizard'])->name('environmentSaveWizard');

    Route::get('requirements', [RequirementsController::class, 'requirements'])->name('requirements');

    Route::get('setting', [SettingController::class, 'index'])->name('setting');
    Route::post('setting/validate', [SettingController::class, 'validate'])->name('settingValidate');
    Route::post('setting/save', [SettingController::class, 'save'])->name('settingSave');

    Route::get('admin', [AdminController::class, 'index'])->name('admin');
    Route::post('admin/validate', [AdminController::class, 'validate'])->name('adminValidate');
    Route::post('admin/save', [AdminController::class, 'save'])->name('adminSave');

    Route::get('final', [FinalController::class, 'finish'])->name('final');
});

Route::group(['prefix' => 'migration', 'as' => 'install.', 'middleware' => 'install'], function () {
    Route::get('database', [DatabaseController::class, 'database'])->name('database');
});

Route::group(['prefix' => env('APP_ADMIN_PREFIX', 'admin').'/upgrade', 'as' => 'update.', 'middleware' => ['web', 'is.admin']], function () {
    Route::group(['middleware' => 'update'], function () {
        Route::get('/', [UpdateController::class, 'welcome'])->name('welcome');
        Route::get('overview', [UpdateController::class, 'overview'])->name('overview');
        Route::get('database', [UpdateController::class, 'database'])->name('database');
    });

    Route::get('final', [UpdateController::class, 'finish'])->name('final');
});
