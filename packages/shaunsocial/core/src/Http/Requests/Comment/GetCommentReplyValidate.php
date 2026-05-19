<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Comment;

class GetCommentReplyValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $commentId, $fail) use ($viewer) {
                    $comment = Comment::findByField('id', $commentId);
                    $viewerId = $viewer ? $viewer->id : 0; 
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $comment) {
                        return $fail(__('The comment is not found.'));
                    }

                    $subject = $comment->getSubject();

                    if (method_exists($subject, 'canViewComment') && ! $subject->canViewComment($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view reply.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'comment_id.required' => __('The comment is required.'),
        ];
    }
}
