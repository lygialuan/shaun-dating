<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Comment;

class DeleteCommentValidate extends BaseFormRequest
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
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $comment) {
                        return $fail(__('The comment is not found.'));
                    }

                    if (! $comment->canDelete($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot delete this comment.'));
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
