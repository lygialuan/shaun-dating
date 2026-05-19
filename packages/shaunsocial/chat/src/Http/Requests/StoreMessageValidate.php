<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatMessageItem;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class StoreMessageValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();
        $type = $this->input('type');
        $types = ChatMessage::getTypes();
        $rules = [
            'type' => ['required', Rule::in($types)],
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
                    }

                    if (! $room->canSendMessage($viewer->id)) {
                        return $fail(__('You cannot send message this chat room.'));
                    }
                },
            ],
        ];

        switch ($this->input('type')) {
            case 'text':
                $rules['content'] = ['required'];
                if (setting('chat.send_text_max')) {
                    $rules['content'][] = 'max:'.setting('chat.send_text_max');
                }
                $this->merge(['items' => []]);

                break;
            case 'file':
            case 'photo':
            case 'link':
                if (setting('chat.send_text_max')) {
                    $rules['content']= 'max:'.setting('chat.send_text_max');
                }
                $rules['items'] = [
                    'required',
                    function ($attribute, $items, $fail) use ($viewer, $type) {
                        switch ($type) {
                            case 'file':
                            case 'photo':
                                $subjectType = 'storage_files';
                                break;
                            case 'link':
                                $subjectType = 'links';
                                break;
                        }

                        if (! is_array($items)) {
                            return $fail(__('The items are not in the list.'));
                        }

                        foreach ($items as $itemId) {
                            $item = ChatMessageItem::findByField('id', $itemId);
                            if (! $item || ! $item->canStore($viewer->id, $subjectType)) {
                                return $fail(__('The item is not exist.'));
                            }
                        }

                        if (setting('chat.send_photo_max') && $type == 'photo' && count($items) > setting('chat.send_photo_max')) {
                            return $fail(__('You can only send :number photos at a time.',['number' => setting('chat.send_photo_max')]));
                        }
                    },
                ];
                break;
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                if(empty($data['parent_message_id'])){
                    return;
                }
                $parentMessage = ChatMessage::findByField('id', $data['parent_message_id']);

                if(! $parentMessage){
                    return $validator->errors()->add('parent_message', __('This message does not exist.'));
                } 
                if($parentMessage->room_id != $data['room_id']){
                    return $validator->errors()->add('parent_message', __('This message does not belong to this room.'));
                }
                if($parentMessage->is_delete){
                    return $validator->errors()->add('parent_message', __('This message has been deleted.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'room_id.required' => __('The room id is required.'),
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'content.required' => __('The content is required.'),
            'photos.required' => __('The photos is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('chat.send_text_max')])
        ];
    }
}
