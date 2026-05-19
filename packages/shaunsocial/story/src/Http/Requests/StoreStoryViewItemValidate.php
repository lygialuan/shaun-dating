<?php


namespace Packages\ShaunSocial\Story\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Story\Models\StoryItem;

class StoreStoryViewItemValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $storyItemId, $fail) use ($viewer) {
                    $storyItem = StoryItem::findByField('id', $storyItemId);
                    if (! $storyItem) {
                        return $fail(__('The story is not found.'));
                    }

                    $story = $storyItem->getStory();
                    
                    if (! $story) {
                        return $fail(__('The story is not found.'));
                    }

                    $viewerId = $viewer->id;
                    $isAdmin = $viewer->isModerator();

                    if (! $story->canView($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view this story.'));
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
