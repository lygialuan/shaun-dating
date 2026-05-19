<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Wallet\Http\Controllers\Api\WalletController;

Route::group(['middleware' => ['auth:sanctum', 'api'], 'prefix' => 'api'], function () {
    Route::get('wallet/get_transactions', [WalletController::class, 'get_transactions'])->name('wallet_get_transactions');
    Route::get('wallet/get_packages', [WalletController::class, 'get_packages'])->name('wallet_get_packages');
    Route::post('wallet/store_deposit', [WalletController::class, 'store_deposit'])->name('wallet_store_deposit');
    Route::get('wallet/config', [WalletController::class, 'config'])->name('wallet_config');
    Route::post('wallet/store_withdraw', [WalletController::class, 'store_withdraw'])->name('wallet_store_withdraw');
    Route::post('wallet/store_send', [WalletController::class, 'store_send'])->name('wallet_store_send');

    Route::post('wallet/store_in_app_purchase_android', [WalletController::class, 'store_in_app_purchase_android'])->name('wallet_store_in_app_purchase_android');
    Route::post('wallet/store_in_app_purchase_apple', [WalletController::class, 'store_in_app_purchase_apple'])->name('wallet_store_in_app_purchase_apple');

    Route::get('wallet/suggest_user_send/{text}', [WalletController::class, 'suggest_user_send'])->name('wallet_suggest_user_send');
    Route::get('wallet/get_withdrawal_info', [WalletController::class, 'get_withdrawal_info'])->name('wallet_get_withdrawal_info');
});
