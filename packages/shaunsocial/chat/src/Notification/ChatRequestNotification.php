<?php


namespace Packages\ShaunSocial\Chat\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class ChatRequestNotification extends BaseNotification
{
    protected $type = 'chat_request';

    public function getExtra()
    {
        $params = $this->notification->getParams();
        $roomId = ! empty($params['room_id']) ? $params['room_id'] : 0;
        return [
            'room_id' => $roomId
        ];
    }

    public function getHref()
    {
        return route('web.chat.request');
    }

    public function getMessage($count)
    {
        if ($count > 1) {
            return __("and others want to chat.");
        } else {
            return __("wants to chat.");
        }
    }
}
