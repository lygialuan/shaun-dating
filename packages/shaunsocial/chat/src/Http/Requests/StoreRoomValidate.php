<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class StoreRoomValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'user_id' => [
                'required',
                'alpha_num',
                function ($attribute, $userId, $fail) use ($viewer) {
                    $viewer = $this->user();
                    $isAdmin = $this->user()->isModerator();

                    if ($viewer->id == $userId) {
                        return $fail(__('You cannot send messages to yourself.'));
                    }

                    $user = User::findByField('id', $userId);
                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }

                    if (ChatRoom::getRoomTwoUser($viewer->id, $userId)) {
                        return;
                    }

                    if (! $user->canSendMessage($viewer->id) && !$isAdmin) {
                        if ($user->isPage()) {
                            return $fail(__('You cannot send messages to this page.'));
                        } else {
                            return $fail(__('You cannot send messages to this user.'));
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_id.required' => __('The user id is required'),
        ];
    }
}
