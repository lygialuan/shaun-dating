<?php 
use Illuminate\Support\Str;

if (! function_exists('getWalletExchangeRate')) {
    function getWalletExchangeRate()
    {
        $exchangeRate = setting('shaun_wallet.exchange_rate');
        return is_numeric($exchangeRate) && $exchangeRate ? $exchangeRate : 1;
    }
}

if (! function_exists('getWalletTokenName')) {
    function getWalletTokenName()
    {
        $tokenName = setting('shaun_wallet.token_name');
        if (! $tokenName) {
            $tokenName = getWalletTokenNameDefault();
        }

        return $tokenName;
    }
}

if (! function_exists('getWalletTokenNameDefault')) {
    function getWalletTokenNameDefault()
    {
        return Str::limit(Str::slug(setting('site.title'),''), 4, '');
    }
}

if (! function_exists('checkEnableFundTransfer')) {
    function checkEnableFundTransfer()
    {
        return setting('shaun_wallet.fund_transfer_enable') && (setting('shaun_wallet.fund_transfer_paypal_enable') || setting('shaun_wallet.fund_transfer_bank_enable'));
    }
}