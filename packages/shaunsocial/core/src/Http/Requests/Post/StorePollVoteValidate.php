<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\PollItem;

class StorePollVoteValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'poll_item_id' => 'required|alpha_num',
            'action' => 'required|string|in:add,remove',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $pollItem = PollItem::findByField('id', $data['poll_item_id']);

                if (! $pollItem) {
                    return $validator->errors()->add('poll_item_id', __('The poll item is not found.'));
                }

                $poll = $pollItem->getPoll();
                $post = $poll->getPost();
                $viewer = $this->user();
                $viewerId = $viewer ? $viewer->id : 0;
                $isAdmin = $viewer ? $viewer->isModerator() : false;
                $isClosed = $poll->isClosed();

                if (! $post) {
                    return $validator->errors()->add('poll_item_id', __('The post is not found.'));
                }

                if (! $post->canView($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('poll_item_id', __('You cannot view this post.'));
                }

                if (! $poll->canVote($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('poll_item_id', __('You cannot vote this poll.'));
                }

                if($isClosed){
                    return $validator->errors()->add('poll_item_id', __('You cannot vote this poll.'));
                }

                switch ($data['action']) {
                    case 'add':
                        if ($pollItem->getVote($viewerId)) {
                            return $validator->errors()->add('poll_item_id', __("You've already voted this item."));
                        }
                        break;
                    case 'remove':
                        if (!$pollItem->getVote($viewerId)) {
                            return $validator->errors()->add('poll_item_id', __("You've already unvoted this item."));
                        }
                        break;
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'poll_item_id.required' => __('The poll item id is required.'),
            'action.required' => __('The action is required.'),
            'action.in' => __('The action is not in the list (add,remove).'),
        ];
    }
}
