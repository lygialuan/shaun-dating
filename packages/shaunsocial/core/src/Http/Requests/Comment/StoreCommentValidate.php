<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Comment;
use Packages\ShaunSocial\Core\Models\CommentItem;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreCommentValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $types = Comment::getTypes();
        unset($types['link']);
        $rules = [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num',
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
                            $item = CommentItem::findByField('id', $itemId);
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
                $viewerId = $this->user()->id;
                $isAdmin = $this->user()->isModerator();
                $data = $validator->getData();

                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportComment')) {
                    return $validator->errors()->add('subject', __('The subject does not support comment.'));
                }

                if (method_exists($subject, 'canComment') && ! $subject->canComment($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot comment.'));
                }

                $this->checkPermissionActionLog('post.comment_max_per_day', 'create_comment', $this->user());
            });
        }
    }

    public function messages()
    {
        return [
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.'),
            'subject_type.required' => __('The subject is required.'),
            'subject_id.required' => __('The subject id is required.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('feature.comment_character_max')])
        ];
    }
}
