<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Packages\ShaunSocial\Chat\Models\ChatMessageUser;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreRoomUnseenValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
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

                    $member = $room->getMember($viewer->id);
                    if ($member->message_count) {
                        return $fail(__('You cannot unread this room.'));
                    }

                    $messageCount = ChatMessageUser::where('user_id', $viewer->id)->where('room_id', $roomId)->where('is_delete', false)->count();
                    if (! $messageCount) {
                        return $fail(__('You cannot unread this room.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required'),
        ];
    }
}
