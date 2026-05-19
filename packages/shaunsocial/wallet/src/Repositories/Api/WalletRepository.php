<?php

namespace Packages\ShaunSocial\Wallet\Repositories\Api;

use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Packages\ShaunSocial\Core\Models\Currency;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Support\Facades\Mail;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\CacheSearchPagination;
use Packages\ShaunSocial\Gateway\Models\Gateway;
use Packages\ShaunSocial\Wallet\Enum\WalletWithdrawStatus;
use Packages\ShaunSocial\Wallet\Http\Resources\WalletPackageResource;
use Packages\ShaunSocial\Wallet\Http\Resources\WalletTransactionResource;
use Packages\ShaunSocial\Wallet\Models\WalletOrder;
use Packages\ShaunSocial\Wallet\Models\WalletPackage;
use Packages\ShaunSocial\Wallet\Models\WalletTransaction;
use Packages\ShaunSocial\Wallet\Models\WalletTransactionType;
use Packages\ShaunSocial\Wallet\Models\WalletWithdraw;
use Packages\ShaunSocial\Wallet\Notification\WallelBalanceNotifyNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletSendNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletWithdrawAcceptNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletWithdrawRejectNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletWithdrawSendNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletSendMassFundNotification;
use Packages\ShaunSocial\Wallet\Notification\WalletSendGiftNotification;
use Packages\ShaunSocial\Wallet\Support\Facades\Wallet;

class WalletRepository
{   
    use CacheSearchPagination;

    public function get_transactions($data, $viewer)
    {
        $builder = WalletTransaction::where('user_id', $viewer->id)->where('is_active', true)->orderBy('id', 'DESC');
        $page = $data['page'];
        $key = $viewer->id.'_'.$data['type'].'_'.$data['date_type'].'_'.$data['from_date'].'_'.$data['to_date'];
        if ($data['type'] != 'all') {
            $builder->where('type', $data['type']);
        }
        switch ($data['date_type']) {
            case '30_day':
                $builder->where('created_at', '>=', now()->subDays(30));
                break;
            case '60_day':
                $builder->where('created_at', '>=', now()->subDays(60));
                break;
            case '90_day':
                $builder->where('created_at', '>=', now()->subDays(90));
                break;
            case 'custom':
                if ($data['from_date']) {
                    $builder->where('created_at', '>=', $data['from_date']. ' 00:00:00');
                }
                if ($data['to_date']) {
                    $builder->where('created_at', '<=', $data['to_date']. ' 23:59:59');
                }
                break;
        }

        $transactions = $this->getCacheSearchPagination($key, $builder, $page, 0, config('shaun_core.cache.time.short'));
        $transactionsNextPage = $this->getCacheSearchPagination($key, $builder, $page + 1, 0, config('shaun_core.cache.time.short'));

        return [
            'items' => WalletTransactionResource::collection($transactions),
            'has_next_page' => count($transactionsNextPage) ? true : false
        ];
    }

    public function get_packages()
    {
        $packages = WalletPackage::getAll();

        return WalletPackageResource::collection($packages);
    }

    public function store_deposit($data, $viewer)
    {
        $amount = $data['amount'];

        if ($data['package_id']) {
            $package = WalletPackage::findByField('id', $data['package_id']);
            $amount = $package['amount'];
        }

        $currencyDefault = Currency::getDefault();
        $exchangeRate = getWalletExchangeRate();
        $order = WalletOrder::create([
            'amount' => round($amount / $exchangeRate,2),
            'currency' => $currencyDefault->code,
            'gateway_id' => $data['gateway_id'],
            'user_id' => $viewer->id,
            'package_id' => $data['package_id'],
            'exchange' => $exchangeRate
        ]);

        $gateway = Gateway::findByField('id', $data['gateway_id']);
        $result = $gateway->createLinkPayment($order);

        return $result;
    }

    public function config()
    {
        return [
            'types' => WalletTransactionType::getAllValue()
        ];
    }

    public function store_withdraw($data, $viewer)
    {
        DB::beginTransaction();
        $result = ['status' => false];
        try {
            $currencyDefault = Currency::getDefault();

            $fee = setting('shaun_wallet.fund_transfer_paypal_fee');
            if ($data['type'] == 'bank') {
                $fee = setting('shaun_wallet.fund_transfer_bank_fee');
            }
            $withdraw = WalletWithdraw::create([
                'user_id' => $viewer->id,
                'amount' => $data['amount'],
                'currency' => $currencyDefault->code,
                'exchange' => getWalletExchangeRate(),
                'bank_account' => $data['bank_account'],
                'type' => $data['type'],
                'fee' => round(($fee * ($data['amount']/getWalletExchangeRate())) / 100, 2),
            ]);
            
            
            $userResult = Wallet::add('withdraw', $viewer->id, - $data['amount'], $withdraw);
            $rootResult = Wallet::add('root_withdraw', config('shaun_wallet.system_wallet_user_id'), $data['amount'], $withdraw, '', $viewer->id);
            if ($userResult['status'] && $rootResult['status']) {
                $result['status'] = true;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }

        // send to notify to admin
        if ($result['status']) {
            $admin = User::findByField('id', config('shaun_core.core.user_root_id'));
            Notification::send($admin, $viewer, WalletWithdrawSendNotification::class, $viewer, [], 'shaun_wallet', false);

            $withdrawalInfo = $viewer->getWithdrawalInfo();

            if ($data['type'] == 'bank') { 
                $withdrawalInfo['bank_detail'] = $data['bank_account'];
            } else {
                $withdrawalInfo['paypal_email'] = $data['bank_account'];
            }

            $viewer->update([
                'withdrawal_info' => json_encode($withdrawalInfo)
            ]);
        }

        return $result;
    }

    public function accept_withdraw($withdraw)
    {
        $withdraw->update([
            'status' => WalletWithdrawStatus::DONE
        ]);

        $user = $withdraw->getUser();
        if ($user) {
            Notification::send($user, $user, WalletWithdrawAcceptNotification::class, null, ['is_system' => true], 'shaun_wallet', false);
        }
    }

    public function reject_withdraw($withdraw)
    {
        DB::beginTransaction();
        $result = ['status' => false];
        try {
            $userResult = Wallet::add('withdraw', $withdraw->user_id, $withdraw->amount, $withdraw);
            $rootResult = Wallet::add('root_withdraw', config('shaun_wallet.system_wallet_user_id'), - $withdraw->amount, $withdraw, '', $withdraw->user_id);
            $withdraw->update([
                'status' => WalletWithdrawStatus::REJECT
            ]);
            if ($userResult['status'] && $rootResult['status']) {
                $result['status'] = true;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }

        $user = $withdraw->getUser();
        if ($result['status'] && $user) {
            Notification::send($user, $user, WalletWithdrawRejectNotification::class, null, ['is_system' => true], 'shaun_wallet', false);
        }

        return $result;
    }

    public function notify_balance($user) {
        Notification::send($user, $user, WallelBalanceNotifyNotification::class, null, ['is_system' => true], 'shaun_wallet', false);
        Mail::send('wallet_balance_notify', $user, [
            'link' => route('web.wallet.index')
        ]);
    }

    public function store_send($data, $viewer, $params = null)
    {
        DB::beginTransaction();
        $result = ['status' => false];
        try {
            $user = User::findByField('id', $data['id']);
            $walletResult = Wallet::transfer('send', $viewer->id, $data['id'], $data['amount'], $user, $viewer, '', $params);
            if ($walletResult['status']) {
                $result['status'] = true;
                $result['transaction_id'] = $walletResult['from_transaction']->id;
                DB::commit();
            } else {
                $result['message'] = __("You don't have enough balance");
                DB::rollBack();
            }
            
        } catch (Exception $e) {
            $result['message'] = $e->getMessage();
            DB::rollBack();
        }

        if ($result['status']) {
            $type = $params['type'] ?? '';
            $notificationClass = $type === 'gift' ? WalletSendGiftNotification::class : WalletSendNotification::class;
            Notification::send($user, $viewer, $notificationClass, null, [], 'shaun_wallet', false);
        }

        return $result;
    }

    public function store_in_app_purchase_android($dataString, $viewer)
    {
        $data = json_decode($dataString, true);
        $data = $data[0];
        $data = json_decode($data['verificationData']['localVerificationData'], true);

        $package = WalletPackage::findByField('google_price_id', $data['productId']);

        $currencyDefault = Currency::getDefault();
        $exchangeRate = getWalletExchangeRate();
        $order = WalletOrder::create([
            'amount' => round($package->amount / $exchangeRate,2),
            'currency' => $currencyDefault->code,
            'gateway_id' => config('shaun_gateway.google_id'),
            'user_id' => $viewer->id,
            'package_id' => $package->id,
            'exchange' => $exchangeRate
        ]);

        $order->onSuccessful($data, $data['orderId']);
    }

    public function store_in_app_purchase_apple($dataArray, $viewer)
    {
        $data = $dataArray[0];
        $transactionId = $data['transactionId'] ?? $data['purchaseID'] ?? null;
        $productId = $data['productID'];
        $order = WalletOrder::findByField('gateway_transaction_id', $transactionId);
        if ($order) {
            $order->onSuccessful($data, $transactionId);
            return;
        }
        $package = WalletPackage::findByField('apple_price_id', $productId);

        $currencyDefault = Currency::getDefault();
        $exchangeRate = getWalletExchangeRate();
        $order = WalletOrder::create([
            'amount' => round($package->amount / $exchangeRate,2),
            'currency' => $currencyDefault->code,
            'gateway_id' => config('shaun_gateway.apple_id'),
            'user_id' => $viewer->id,
            'package_id' => $package->id,
            'exchange' => $exchangeRate
        ]);

        $order->onSuccessful($data, $transactionId);
    }


    public function send_mass_fund($data, $viewer)
    {
        DB::beginTransaction();

        $user = User::findByField('id', $data['id']);
        try {
            Wallet::add('buy', $data['id'], $data['amount'], $user, 'mass_fund');
            Wallet::add('root_buy', config('shaun_wallet.system_wallet_user_id'), -$data['amount'], $user, 'mass_fund', $data['id']);

            DB::commit();
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }

        if ($data['notify']) {
            Notification::send($user, $viewer, WalletSendMassFundNotification::class, null, [], 'shaun_wallet', false);
        }
    }

    public function get_withdrawal_info($viewer)
    {
        return $viewer->getWithdrawalInfo();
    }
}
