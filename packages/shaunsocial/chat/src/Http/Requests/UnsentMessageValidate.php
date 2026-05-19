<?php


namespace Packages\ShaunSocial\Chat\Http\Requests;

use Packages\ShaunSocial\Chat\Models\ChatMessage;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class UnsentMessageValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $messageId, $fail) use ($viewer) {
                    $message = ChatMessage::findByField('id', $messageId);
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $message) {
                        return $fail(__('The message is not found.'));
                    }
                    if($message->is_delete){
                        return $fail(__('The message has been deleted.')); 
                    }
                    if (! $message->canDelete($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot unsent this message.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
