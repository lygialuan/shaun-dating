<?php


namespace Packages\ShaunSocial\Story\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Story\Models\StoryItem;
use Packages\ShaunSocial\Core\Models\User;

class ShareStoryValidate extends BaseFormRequest
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

                    // $viewerId = $viewer->id;
                    // $isAdmin = $viewer->isModerator();

                    if (! $storyItem->story_id) {
                        return $fail(__('The story is not found.'));
                    }

                    // $canSendMessage = ($storyItem->getUser()->canSendMessage($viewerId) || $isAdmin) && $viewer->id != $storyItem->user_id;
                    // if (! $canSendMessage) {
                    //     return $fail(__('You cannot send message to this story.'));
                    // }
                },
            ],
            'user_ids' => [
                'required',
                function ($attribute, $userIds, $fail) use ($viewer) {
                    if (count($userIds) > getStoryShareUserMax()) {
                        return $fail(__('The maximum number of users sent each time is :max', ['max' => getStoryShareUserMax()]));
                    }

                    foreach ($userIds as $userId) {
                        $user =  User::findByField('id', $userId);
                        if (!$user) {
                            return $fail(__('The user does not exist.'));
                        }

                        if ($userId == $viewer->id) {
                            return $fail(__('You cannot share story to yourself.'));
                        }
        
                        if ($user->checkBlock($viewer->id)) {
                            return $fail(__('You cannot share story this user.'));
                        }
                    }
                }
            ]
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
            'user_ids.required' => __('The user is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('chat.send_text_max')])
        ];
    }
}
