<?php

namespace Packages\ShaunSocial\Gift\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Gift\Models\Gift;

class SendGiftRequest extends BaseFormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'receiver_id' => 'required|integer',
            'gift_id'     => 'required|integer',
            'quantity'    => 'required|integer|min:1',
            'target_type' => 'required|string',
            'target_id'   => 'required|integer',
            'password'    => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (!$validator->fails()) {
            $validator->after(function ($validator) {
                $sender = $this->user();

                $receiver = User::find($this->receiver_id);

                if (!$receiver) {
                    throw new MessageHttpException(__('User not found.'));
                }

                if ($sender->id == $receiver->id) {
                    throw new MessageHttpException(__('You cannot send gift to yourself.'));
                }

                if (!$receiver->canUseGift()) {
                    throw new MessageHttpException(__('This user cannot receive gifts.'));
                }

                $gift = Gift::find($this->gift_id);
                if (!$gift) {
                    throw new MessageHttpException(__('Gift not found.'));
                }
            });
        }
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.')
        ];
    }
}