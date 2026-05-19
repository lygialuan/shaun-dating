<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\AiProvider\Http\Controllers\Admin\AiProviderController;
use Packages\ShaunSocial\AiProvider\Http\Controllers\Admin\AiProviderKeyController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| Routes are defined here and loaded by the package service provider.
|
*/

Route::group([
    'prefix' => env('APP_ADMIN_PREFIX', 'admin'),
    'as' => 'admin.',
    'middleware' => ['web', 'is.admin'],
], static function (): void {
    Route::get('/ai-providers', [AiProviderController::class, 'index'])->name('ai_provider.index');
    Route::get('/ai-providers/{provider}/keys', [AiProviderKeyController::class, 'index'])->name('ai_provider.keys.index');
    Route::get('/ai-providers/{provider}/keys/create', [AiProviderKeyController::class, 'create'])->name('ai_provider.keys.create');
    Route::get('/ai-providers/{provider}/keys/{key}/edit', [AiProviderKeyController::class, 'edit'])->name('ai_provider.keys.edit');
    Route::post('/ai-providers/keys/store', [AiProviderKeyController::class, 'store'])->name('ai_provider.keys.store');
    Route::get('/ai-providers/keys/{key}/delete', [AiProviderKeyController::class, 'destroy'])->name('ai_provider.keys.destroy');
});
