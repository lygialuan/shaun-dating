<?php

namespace Packages\ShaunSocial\Chat\Http\Requests;

use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Chat\Models\ChatRoom;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\FileValidation;

class StoreAudioValidate extends BaseFormRequest
{
    public function authorize()
    {
        return setting('feature.ffmpeg_enable');
    }

    public function rules()
    {
        $viewer = $this->user();
        return [
            'file' => ['required', new FileValidation('mp3')],
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
            'file.required' => __('The file is required.'),
            'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.',
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You can not upload audio.'));
    }
}
