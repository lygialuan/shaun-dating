<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\CommentReply;

class EditCommentReplyValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $replyId, $fail) use ($viewer) {
                    $reply = CommentReply::findByField('id', $replyId);
                    $isAdmin = $viewer->isModerator();

                    if (! $reply) {
                        return $fail(__('The reply is not found.'));
                    }

                    if (! $reply->canEdit($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot edit this reply.'));
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
                $reply = CommentReply::findByField('id', $data['id']);

                if (empty($data['content'])) {
                    return $validator->errors()->add('content', __('The content is required.'));
                }
                $content = $reply->getMentionContent(null);
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
