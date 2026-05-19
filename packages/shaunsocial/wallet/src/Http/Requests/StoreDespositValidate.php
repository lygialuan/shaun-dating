<?php

namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Currency;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\Gateway\Models\Gateway;
use Packages\ShaunSocial\Wallet\Models\WalletPackage;

class StoreDespositValidate extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'gateway_id' => [
                'required',
                function ($attribute, $gatewayId, $fail) {
                    $gateway = Gateway::findByField('id', $gatewayId);
                    if (! $gateway || ! $gateway->is_active) {
                        return $fail(__('The gateway does not exist.'));
                    }

                    $currencyDefault = Currency::getDefault();
                    if (! $gateway->checkSupportCurrency($currencyDefault->code)) {
                        return $fail(__('The gateway does not support currency.'));
                    }
                },
            ],
        ];

        $packageId = $this->input('package_id');
        if ($packageId) {
            $rules['package_id'] = [
                'required',
                function ($attribute, $packageId, $fail) {
                    $package = WalletPackage::findByField('id', $packageId);
                    if (! $package || ! $package->is_active) {
                        return $fail(__('The package does not exist.'));
                    }
                },
            ];
        } else {
            $rules['amount'] = ['required', new AmountValidation(), 'numeric', 'min:1'];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $amount = 0;
                if (! empty($data['package_id'])) {
                    $package = WalletPackage::findByField('id', $data['package_id']);
                    $amount = $package->amount;
                } else {
                    $amount = $data['amount'];
                }
                $currencyDefault = Currency::getDefault(); 
                $gateway = Gateway::findByField('id', $data['gateway_id']);
                $exchangeRate = getWalletExchangeRate();
                $priceMax = $gateway->getClass()->getMaxPrice();
                $amount = round($amount / $exchangeRate,2);
                if ($amount > $priceMax) {
                    return $validator->errors()->add('amount', __('The amount cannot be greater than'). ' ' .formatNumber($priceMax) .' '.$currencyDefault->code.'.');
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'gateway_id.required' => __('Please select Payment method.'),
            'package_id.required' => __('The package id is required.'),
            'amount.required' => __('The amount is required.'),
            'amount.integer' => __('The amount must be integer.'),
            'amount.min' => __('The amount must be minimum 1.'),
        ];
    }
}
