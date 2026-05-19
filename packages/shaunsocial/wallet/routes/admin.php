<?php

use Illuminate\Support\Facades\Route;
use Packages\ShaunSocial\Wallet\Http\Controllers\Admin\WalletPackageController;
use Packages\ShaunSocial\Wallet\Http\Controllers\Admin\WalletController;
use Packages\ShaunSocial\Wallet\Http\Controllers\Admin\WalletWithdrawController;
use Packages\ShaunSocial\Wallet\Http\Controllers\Admin\WalletFundController;

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
    Route::get('/wallets', [WalletController::class, 'index'])->name('wallet.index');
    Route::get('/wallets/transactions/{user_id}', [WalletController::class, 'transactions'])->where('user_id', '[0-9]+')->name('wallet.transactions');

    Route::get('/wallets/packages', [WalletPackageController::class, 'index'])->name('wallet.package.index');
    Route::get('/wallets/packages/create/{id?}', [WalletPackageController::class, 'create'])->where('id', '[0-9]+')->name('wallet.package.create');
    Route::post('/wallets/packages/store', [WalletPackageController::class, 'store'])->name('wallet.package.store');
    Route::get('/wallets/packages/delete/{id}', [WalletPackageController::class, 'delete'])->where('id', '[0-9]+')->name('wallet.package.delete');
    Route::post('/wallets/packages/store_order', [WalletPackageController::class, 'store_order'])->name('wallet.package.store_order');

    Route::get('/wallets/withdraws', [WalletWithdrawController::class, 'index'])->name('wallet.withdraw.index');
    Route::get('/wallets/withdraws/store_accept/{id}', [WalletWithdrawController::class, 'store_accept'])->where('id', '[0-9]+')->name('wallet.withdraw.store_accept');
    Route::get('/wallets/withdraws/store_reject/{id}', [WalletWithdrawController::class, 'store_reject'])->where('id', '[0-9]+')->name('wallet.withdraw.store_reject');
    Route::get('/wallets/withdraws/detail/{id}', [WalletWithdrawController::class, 'detail'])->where('id', '[0-9]+')->name('wallet.withdraw.detail');
    Route::post('/wallets/withdraws/store_manage', [WalletWithdrawController::class, 'store_manage'])->name('wallet.withdraw.store_manage');

    Route::get('/wallets/funds', [WalletFundController::class, 'index'])->name('wallet.fund.index');
    Route::post('/wallets/funds/send', [WalletFundController::class, 'send'])->name('wallet.fund.send_fund');

    Route::get('/wallets/billing_activity', [WalletController::class, 'billing_activity'])->name('wallet.billing_activity');
});