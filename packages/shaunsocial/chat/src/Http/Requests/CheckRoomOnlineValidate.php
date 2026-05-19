<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class CheckRoomOnlineValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'ids' => [
                'required',
                function ($attribute, $ids, $fail) use ($viewer) {
                    if (! is_array($ids)) {
                        return $fail(__('The ids is not in the list.'));
                    }

                    foreach ($ids as $id) {
                        $room = ChatRoom::findByField('id', $id);
                        if (!$room || !$room->canView($viewer->id)) {
                            return $fail(__('The chat room is not found.'));
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'ids.required' => __('The ids is required.'),
        ];
    }
}
