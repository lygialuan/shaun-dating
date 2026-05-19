<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentReply;
use Packages\ShaunSocial\Core\Models\CommentReplyItem;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreCommentReplyValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $viewer = $this->user();
        $types = CommentReply::getTypes();
        unset($types['link']);
        $rules = [
            'comment_id' => [
                'required',
                'alpha_num',
                function ($attribute, $commentId, $fail) use ($viewer) {
                    $isAdmin = $viewer ? $viewer->isModerator() : false;
                    $comment = Comment::findByField('id', $commentId);

                    if (! $comment) {
                        return $fail(__('The comment is not found.'));
                    }

                    $subject = $comment->getSubject();
                    if (! $subject) {
                        return $fail(__('The subject is not found.'));
                    }

                    if (method_exists($subject, 'canComment') && ! $subject->canComment($viewer->id) && !$isAdmin) {
                        return $fail(__('The subject cannot comment.'));
                    }
                },
            ],
            'type' => ['required', Rule::in($types)]
        ];

        $viewer = $this->user();

        switch ($this->input('type')) {
            case 'text':
                $rules['content'] = ['required'];
                $this->merge(['items' => []]);
                break;
            case 'photo':
                $rules['content'] = ['nullable', 'string'];
                $rules['items'] = [
                    'required',
                    function ($attribute, $items, $fail) use ($viewer) {
                        if (! is_array($items)) {
                            return $fail(__('The items are not in the list.'));
                        }

                        if (! count($items)) {
                            return $fail(__('The item is not exist.'));
                        }

                        if (setting('feature.comment_photo_max') && count($items) > setting('feature.comment_photo_max')) {
                            return $fail(__('You can only upload :number files at a time.',['number' => setting('feature.comment_photo_max')]));
                        }

                        foreach ($items as $itemId) {
                            $item = CommentReplyItem::findByField('id', $itemId);
                            if (! $item || ! $item->canStore($viewer->id, 'storage_files')) {
                                return $fail(__('The item is not exist.'));
                            }
                        }                  
                    },
                ];
                break;
        }

        if (setting('feature.comment_character_max')) {
            $rules['content'][] = 'max:'.setting('feature.comment_character_max');
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $this->checkPermissionActionLog('post.comment_max_per_day', 'create_comment', $this->user());
            });
        }
    }

    public function messages()
    {
        return [
            'comment_id.required' => __('The comment is required.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('feature.comment_character_max')]),
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.')
        ];
    }
}
