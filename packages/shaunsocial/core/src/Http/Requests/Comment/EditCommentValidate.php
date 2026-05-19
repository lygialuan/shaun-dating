<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Comment;

class EditCommentValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $commnetId, $fail) use ($viewer) {
                    $comment = Comment::findByField('id', $commnetId);
                    $isAdmin = $viewer->isModerator();

                    if (! $comment) {
                        return $fail(__('The comment is not found.'));
                    }

                    if (! $comment->canEdit($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot edit this comment.'));
                    }

                },
            ],
            'content' => 'string|nullable'
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $comment = Comment::findByField('id', $data['id']);

                if (empty($data['content'])) {
                    return $validator->errors()->add('content', __('The content is required.'));
                }
                $content = $comment->getMentionContent(null);
                if ($data['content'] == $content) {
                    return $validator->errors()->add('content', __('The content must be different.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
