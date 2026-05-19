<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Models\PollItem;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetPollVoteValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'poll_item_id' => [
                'required',
                'alpha_num',
                function ($attribute, $pollItemId, $fail) use ($viewer) {
                    $pollItem = PollItem::findByField('id', $pollItemId);

                    if (! $pollItem) {
                        return $fail(__('The poll item is not found.'));
                    }

                    $poll = $pollItem->getPoll();
                    $post = $poll->getPost();
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $post) {
                        return $fail('poll_item_id', __('The post is not found.'));
                    }

                    if (! $post->canView($viewerId) && !$isAdmin) {
                        return $fail('poll_item_id', __('You cannot view this post.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'poll_item_id.required' => __('The poll item id is required.'),
        ];
    }
}
