<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class StoreMessageSendFundValidate extends BaseFormRequest
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
        $viewer = $this->user();
        return [
            'room_id' => [
                'required',
                'alpha_num',
                function ($attribute, $roomId, $fail) use ($viewer) {
                    $room = ChatRoom::findByField('id', $roomId);

                    if (! $room) {
                        return $fail(__('The chat room is not found.'));
                    }

                    if (! $room->canView($viewer->id)) {
                        return $fail(__('This chat room is unavailable.'));
                    }

                    //check user delete
                    if (! $room->is_group ) {
                        $members = $room->getMembers();
                        $member = $members->first(function ($member, $key) use ($viewer) {
                            return ($viewer->id != $member->user_id);
                        });

                        if (! User::findByField('id', $member->user_id)) {
                            return $fail(__('This chat room is unavailable.'));
                        }
                    } else {
                        return $fail(__('You cannot send message this chat room.'));
                    }

                    if (! $room->canSendMessage($viewer->id)) {
                        return $fail(__('You cannot send message this chat room.'));
                    }
                },
            ],
            'user_id' => 'required|alpha_num',
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
                $user = User::findByField('id', $data['user_id']);
                $room = ChatRoom::findByField('id',  $data['room_id']);

                if ($data['user_id'] == $viewer->id) {
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

                $members = $room->getMembers();
                $member = $members->first(function ($member, $key) use ($viewer) {
                    return ($viewer->id != $member->user_id);
                });
                if($member->user_id != $data['user_id']){
                    return $validator->errors()->add('user', __('This user does not belong to this room.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'room_id.required' => __('The room id is required.'),
            'user_id.required' => __('The user id is required.'),
            'password.required' => __('The password is required.'),
            'amount.required' => __('The amount is required.')
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You can not transfer fund your wallet.'));
    }
}
