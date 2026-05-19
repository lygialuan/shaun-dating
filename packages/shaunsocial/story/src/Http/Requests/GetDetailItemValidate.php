<?php


namespace Packages\ShaunSocial\Story\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Story\Models\StoryItem;

class GetDetailItemValidate extends BaseFormRequest
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
                    $viewerId = $viewer->id;
                    
                    if (! $storyItem->isOwner($viewerId)) {
                        if (! $story || ! $story->canView($viewerId)) {
                            return $fail(__('The story is not found.'));
                        }                        
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
