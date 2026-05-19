<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\PaidContent\Models\TipPackage;

class StoreTipValidate extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
            'user_id' => [
                'required',
                function ($attribute, $userId, $fail) {
                    $viewer = $this->user();
                    $user = User::findByField('id', $userId);
                    if (! $user || ! $user->canTip($viewer->id)) {
                        return $fail(__('The user is not found.'));
                    }
                },
            ]
        ];

        $packageId = $this->input('package_id');
        if ($packageId) {
            $rules['package_id'] = [
                'required',
                function ($attribute, $packageId, $fail) {
                    $package = TipPackage::findByField('id', $packageId);
                    if (! $package || ! $package->is_active || $package->isDeleted()) {
                        return $fail(__('The package does not exist.'));
                    }
                },
            ];
        } else {
            $rules['amount'] = ['required', 'numeric', 'min:1', 'decimal:0,'.config('shaun_core.core.decimal')];
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $user =  $this->user();
                $amount = $data['amount'] ?? 0;
                if (!empty($data['package_id'])) {
                    $package = TipPackage::findByField('id', $data['package_id']);
                    $amount = $package->amount;
                }

                if ($user->getCurrentBalance() < $amount) {
                    $validator->errors()->add('user', __("You don't have enough balance"));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.'),
            'package_id.required' => __('The package is required.'),
            'amount.required' => __('The amount is required.'),
            'amount.integer' => __('The amount must be integer.'),
            'amount.min' => __('The amount must be minimum 1.'),
            'amount.decimal' => __('The amount field must have 0-:number decimal places.',[
                'number' => config('shaun_core.core.decimal')
            ]),
        ];
    }
}
