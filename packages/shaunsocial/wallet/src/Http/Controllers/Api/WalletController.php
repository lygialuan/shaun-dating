<?php


namespace Packages\ShaunSocial\Wallet\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Repositories\Api\UserRepository;
use Packages\ShaunSocial\Wallet\Http\Requests\StoreSendValidate;
use Packages\ShaunSocial\Wallet\Http\Requests\GetTransactionValidate;
use Packages\ShaunSocial\Wallet\Http\Requests\StoreDespositValidate;
use Packages\ShaunSocial\Wallet\Http\Requests\StoreInAppPurchaseAndroidValidate;
use Packages\ShaunSocial\Wallet\Http\Requests\StoreInAppPurchaseAppleValidate;
use Packages\ShaunSocial\Wallet\Http\Requests\StoreWithdrawValidate;
use Packages\ShaunSocial\Wallet\Repositories\Api\WalletRepository;

class WalletController extends ApiController
{
    protected $walletRepository;
    protected $userRepository;

    public function __construct(WalletRepository $walletRepository, UserRepository $userRepository)
    {
        if (! setting('shaun_wallet.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }
        
        $this->walletRepository = $walletRepository;
        $this->userRepository = $userRepository;
    }
    
    public function get_transactions(GetTransactionValidate $request)
    {
        $request->mergeIfMissing([
            'page' => 1,
            'from_date' => '',
            'to_date' => '',
        ]);
        
        $result = $this->walletRepository->get_transactions($request->only([
            'page', 'from_date', 'to_date', 'type' , 'date_type'
        ]), $request->user());
    
        return $this->successResponse($result);
    }

    public function get_packages()
    {
        $result = $this->walletRepository->get_packages();
        
        return $this->successResponse($result);
    }

    public function store_deposit(StoreDespositValidate $request)
    {
        $request->mergeIfMissing([
            'package_id' => 0,
            'amount' => '0',
        ]);

        $result = $this->walletRepository->store_deposit($request->only([
            'gateway_id', 'package_id', 'amount'
        ]), $request->user());

        return $this->successResponse($result);
    }

    public function config()
    {
        $result = $this->walletRepository->config();

        return $this->successResponse($result);
    }

    public function store_withdraw(StoreWithdrawValidate $request)
    {
        $result = $this->walletRepository->store_withdraw($request->only([
            'type', 'amount', 'bank_account'
        ]), $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function store_send(StoreSendValidate $request)
    {
        $result = $this->walletRepository->store_send($request->only([
            'id', 'amount'
        ]), $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function store_in_app_purchase_android(StoreInAppPurchaseAndroidValidate $request)
    {
        $this->walletRepository->store_in_app_purchase_android($request->data, $request->user());
        
        return $this->successResponse();
    }

    public function store_in_app_purchase_apple(StoreInAppPurchaseAppleValidate $request)
    {
        $this->walletRepository->store_in_app_purchase_apple($request->data, $request->user());
        
        return $this->successResponse();
    }

    public function suggest_user_send(Request $request)
    {
        $result = $this->userRepository->search($request->text,[
            'not_me' => true,
        ], $request->user());

        return $this->successResponse($result);
    }

    public function get_withdrawal_info(Request $request)
    {
        $result = $this->walletRepository->get_withdrawal_info($request->user());

        return $this->successResponse($result);
    }
}