<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Notification;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserNotification;

class StoreNotificationSeenValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $notification = UserNotification::findByField('id', $id);
                    if (! $notification || ! $notification->isOwner($viewer->id)) {
                        return $fail(__('The notification is not found.'));
                    }
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'id' => __('The id is required.')
        ];
    }
}
