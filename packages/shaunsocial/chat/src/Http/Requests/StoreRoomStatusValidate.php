<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreRoomStatusValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'action' => ['required', 'in:accept,delete,block'],
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
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();

                $data = $validator->getData();
                $room = ChatRoom::findByField('id', $data['room_id']);
                $member = $room->getMember($viewer->id);

                if ($member->status != 'sent') {
                    return $validator->errors()->add('room_id', __('You cannot change the status of this chat room.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'room_id.required' => __('The id is required.'),
            'action.required' => __('The action is required.'),
            'action.in' => __('The action is not in the list (accept,delete,block).'),
        ];
    }
}
