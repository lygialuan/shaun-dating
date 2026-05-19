<?php


namespace Packages\ShaunSocial\Story\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Story\Models\StoryItem;

class StoreMessageValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        $rules = [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $storyItemId, $fail) use ($viewer) {
                    $storyItem = StoryItem::findByField('id', $storyItemId);
                    
                    if (! $storyItem) {
                        return $fail(__('The story is not found.'));
                    }

                    $viewerId = $viewer->id;
                    $isAdmin = $viewer->isModerator();

                    if (! $storyItem->story_id) {
                        return $fail(__('The story is not found.'));
                    }

                    $canSendMessage = ($storyItem->getUser()->canSendMessage($viewerId) || $isAdmin) && $viewer->id != $storyItem->user_id;
                    if (! $canSendMessage) {
                        return $fail(__('You cannot send message to this story.'));
                    }
                },
            ],
            'content' => ['required']
        ];

        if (setting('chat.send_text_max')) {
            $rules['content'][] = 'max:'.setting('chat.send_text_max');
        }

        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('chat.send_text_max')])
        ];
    }
}
