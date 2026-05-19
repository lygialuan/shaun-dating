<?php


namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class StoreSendValidate extends BaseFormRequest
{
    public function authorize()
    {
        $viewer = $this->user();
        $canShowSendWallet = $viewer->canShowSendWallet();
        if ($canShowSendWallet) {
            return $viewer->hasPermission('wallet.send_fund');
        }

        return false;
    }

    public function rules()
    {
        return [
            'id' => 'required|alpha_num',
            'amount' => ['required', new AmountValidation()],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $viewer = $this->user();
                $isAdmin = $this->user()->isModerator();
                $user = User::findByField('id', $data['id']);

                if ($data['id'] == $viewer->id) {
                    return $validator->errors()->add('user', __('You cannot send funds to yourself.'));
                }

                if (! $user) {
                    return $validator->errors()->add('user', __('The user is not found.'));
                }

                if ($user->checkBlock($viewer->id) && !$isAdmin) {
                    return $validator->errors()->add('user', __('You cannot send funds this user.'));
                }

                if ($viewer->getCurrentBalance() < $data['amount'])  {
                    $validator->errors()->add('user', __("You don't have enough balance"));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The user id is required.'),
            'password.required' => __('The password is required.'),
            'amount.required' => __('The amount is required.')
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You can not send fund your wallet.'));
    }
}
