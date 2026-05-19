<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\CommentReply;

class DeleteCommentReplyValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $commentId, $fail) use ($viewer) {
                    $reply = CommentReply::findByField('id', $commentId);
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $reply) {
                        return $fail(__('The reply is not found.'));
                    }

                    if (! $reply->canDelete($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot delete this reply.'));
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
