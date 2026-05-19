<?php

namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\Currency;
use Packages\ShaunSocial\Core\Validation\AmountValidation;

class StoreWithdrawValidate extends BaseFormRequest
{
    public function authorize()
    {
        $viewer = $this->user();
        $canShowWithdrawWallet = $viewer->canShowWithdrawWallet();
        $enableFundTransfer = checkEnableFundTransfer();
        if ($enableFundTransfer && $canShowWithdrawWallet) {
            if (setting('shaun_wallet.fund_transfer_verify_enable')) {
                $viewer = $this->user();
                return $viewer->isVerify();
            }

            return $viewer->hasPermission('wallet.transfer_fund');
        }

        return false;
    }

    public function rules()
    {
        $types = [];

        if (setting('shaun_wallet.fund_transfer_paypal_enable')) {
            $types[] = 'paypal';
        }

        if (setting('shaun_wallet.fund_transfer_bank_enable')) {
            $types[] = 'bank';
        }

        $rules = [
            'type' => [
                'required',
                Rule::in($types)
            ],
            'amount' => [
                'required',
                new AmountValidation()
            ],
            'bank_account' => 'required|string'
        ];
        $type = $this->input('type');
        if ($type == 'paypal') {
            $rules['bank_account_confirmed'] = 'required|email';
            $rules['bank_account'] = 'required|email|max:255';
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $user =  $this->user();
                $minimum = setting('shaun_wallet.fund_transfer_paypal_minimum');
                if ($data['type'] == 'bank') {
                    $minimum = setting('shaun_wallet.fund_transfer_bank_minimum');
                }

                if ($data['type'] == 'paypal' && $data['bank_account_confirmed'] != $data['bank_account']) {
                    return $validator->errors()->add('bank_account_confirmed', __('The confirm email does not match.'));
                }
                $currencyDefault = Currency::getDefault();
                
                $amount = $data['amount'] / getWalletExchangeRate();
                if ($amount < $minimum) {
                    $validator->errors()->add('amount', __('The amount must be minimum :minimum', ['minimum' => $minimum]).' '.$currencyDefault->code.'.');
                }

                if ($user->getCurrentBalance() < $data['amount']) {
                    $validator->errors()->add('user', __("You don't have enough balance"));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {        
        $messages = [
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'amount.required' => __('The amount is required.'),
            'amount.integer' => __('The amount must be integer.'),
            'bank_account.required' => __('The bank account is required.')
        ];

        if ($this->input('type') == 'paypal') {
            $messages['bank_account.required']  = __('The email paypal is required.');
            $messages['bank_account.email']  = __('The email paypal should be valid.');
            $messages['bank_account_confirmed.required']  = __('Re-type Paypal email is required .');
            $messages['bank_account_confirmed.email']  = __('Re-type Paypal email confirm should be valid.');
        }

        return $messages;
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You can not transfer fund your wallet.'));
    }
}
