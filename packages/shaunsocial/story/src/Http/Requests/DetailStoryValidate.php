<?php


namespace Packages\ShaunSocial\Story\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Story\Models\Story;

class DetailStoryValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $storyId, $fail) use ($viewer) {
                    $story = Story::findByField('id', $storyId);
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $story) {
                        return $fail(__('The story is not found.'));
                    }

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
