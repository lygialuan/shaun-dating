<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Enum\CommentPrivacy;
use Packages\ShaunSocial\Core\Enum\PostPaidType;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Models\PostItem;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;
use Packages\ShaunSocial\Core\Models\StorageFile;
use Packages\ShaunSocial\Core\Validation\AmountValidation;

class StorePostValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $types = array_keys(Post::getTypes());
        unset($types['vibb']);

        $commentPrivacies = CommentPrivacy::values();
        $rules = [
            'type' => ['required', Rule::in($types)],
            'comment_privacy' => ['required', Rule::in($commentPrivacies)],
            'source_type' => ['nullable'],
            'source_id' => ['nullable'],
        ];

        $viewer = $this->user();
        $type = $this->input('type');

        switch ($this->input('type')) {
            case 'text':
                $rules['content'] = ['required'];
                $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));
                $this->merge(['items' => [],'parent_id' => 0]);

                break;
            case 'share_item':
                $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));

                $this->merge(['items' => [],'parent_id' => 0]);
                $rules += [
                    'subject_type' => 'required',
                    'subject_id' => 'required'
                ];
                break;
            case 'file':
            case 'photo':
            case 'link':
            case 'video':
            case 'poll':
                $this->merge(['parent_id' => 0]);
                $rules['items'] = [
                    'required',
                    function ($attribute, $items, $fail) use ($viewer, $type) {
                        $subjectType = '';
                        switch ($type) {
                            case 'file':
                            case 'photo':
                                $subjectType = 'storage_files';
                                break;
                            case 'link':
                                $subjectType = 'links';
                                break;
                            case 'video':
                                $subjectType = 'videos';
                                break;
                        }

                        if (! is_array($items)) {
                            return $fail(__('The items are not in the list.'));
                        }

                        if (! count($items)) {
                            return $fail(__('The item is not exist.'));
                        }

                        if ($type == 'poll') {
                            $items = array_unique($items);
                            $this->checkPermission('post.allow_create_poll', $viewer);
                            $this->checkPermissionHaveValue('post.max_poll_item', count($items), $viewer);
                            foreach ($items as $item) {
                                if (strlen($item) > 255) {
                                    return $fail(__('The poll item must not be greater than 255 characters.'));
                                }
                            }
                            return;
                        }

                        foreach ($items as $itemId) {
                            $item = PostItem::findByField('id', $itemId);
                            if (! $item || ! $item->canStore($viewer->id, $subjectType)) {
                                return $fail(__('The item is not exist.'));
                            }
                        }

                        if (setting('feature.post_photo_max') && $type == 'photo' && count($items) > setting('feature.post_photo_max')) {
                            return $fail(__('You can only share :number photos at a time.',['number' => setting('feature.post_photo_max')]));
                        }

                        if (in_array($type, ['link', 'video']) && count($items) > 1) {  
                            return $fail(__('The item is not exist.'));
                        }

                        if (setting('post.post_file_max') && $type == 'file' && count($items) > setting('post.post_file_max')) {
                            return $fail(__('You can only upload :number files at a time.',['number' => setting('post.post_file_max')]));
                        }
                    },
                ];
                if ($type == 'poll') {
                    $rules['content'] = ['required'];
                    $rules['close_minute'] = ['required', 'min:1', 'integer', function ($attribute, $closeMinute, $fail) use ($viewer) {
                        $this->checkPermissionHaveValue('post.max_close_day', $closeMinute / (24*60), $viewer);
                    }];
                }

                $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));
                break;
            case 'share':
                $rules['parent_id'] = [
                    'required',
                    function ($attribute, $parentId, $fail) use ($viewer, $type) {
                        $this->merge(['items' => []]);

                        $post = Post::findByField('id', $parentId);
                        if (!($post)) {
                            return $fail(__('The post does not exist.'));
                        }

                        if (!$post->canShare($viewer->id)) {
                            return $fail(__('You cannot share this post.'));
                        }
                    },
                ];
                $rules['content'] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));
                break;
        }
        $checkPaid = false;
        if(in_array($type, ['photo', 'video'])){
            $rules['content_warning_categories'] = [
                'nullable',
                function ($attribute, $contentWarningCategories, $fail) {
                    if (! $contentWarningCategories) {
                        return;
                    }

                    if (!is_array($contentWarningCategories)) {
                        return $fail(__('The content warning category is not in the list.'));
                    }
                    
                    $check = true;
                    foreach ($contentWarningCategories as $id) {
                        if ($id) {
                            $category = ContentWarningCategory::findByField('id' ,$id);
                            if (!$category || $category->isDeleted()) {
                                $check = false;
                            }
                        }
                    }

                    if (!$check) {
                        return $fail(__('The content warning category is not found.'));
                    }
                },
            ];
            
            if ($viewer->canCreatePostPaidContent() && ! $this->input('source_type')) {
                $checkPaid = true;
                $rules['is_paid'] = [
                    'boolean'
                ];
                
                $rules['paid_type'] = [
                    'nullable',
                    Rule::in(PostPaidType::values())
                ];

                if ($this->input('paid_type') == PostPaidType::PAYPERVIEW->value) {
                    $rules['content_amount'] = ['required', new AmountValidation(), 'numeric', 'min:1'];
                } else {
                    $this->merge([
                        'content_amount' => 0,
                    ]);
                }

                if ($this->input('is_paid')) {
                    $rules['paid_type'] = [
                        'required',
                        Rule::in(PostPaidType::values())
                    ];
                    $rules['thumb_file_id'] = [
                        function ($attribute, $thumbFileId, $fail) use ($viewer) {
                            if ($thumbFileId) {
                                $thumb = StorageFile::findByField('id', $thumbFileId);
                                if (! $thumb || ! $thumb->canStore($viewer->id, 'post_review')) {
                                    return $fail(__('The thumb is not found.'));
                                }
                            }
                        },
                    ];
                }
            }
        }
        if (! $checkPaid) {
            $this->merge([
                'thumb_file_id' => 0,
                'is_paid' => 0,
                'content_amount' => 0,
                'paid_type' => PostPaidType::PAYPERVIEW->value
            ]);
        }

        return $rules;
    }

    public function prepareForValidation()
    {
        parent::prepareForValidation();
        
        $this->mergeIfMissing([
            'comment_privacy' => 'everyone'
        ]);
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                
                $data = $validator->getData();
                switch ($data['type']) {
                    case 'share_item':
                        $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                        if (! $subject || ! method_exists($subject, 'isSubject') || ! $subject->isSubject() || ! $subject->canShareProfile($user->id)) {
                            return $validator->errors()->add('subject', __('The subject does not exist.'));
                        }
                        break;
                }

                $this->checkPermissionHaveValue('post.character_max', strlen($data['content']), $user);
                $this->checkPermissionActionLog('post.max_per_day', 'create_post', $user);
                $check = true;
                if (! empty($data['source_type']) && ! empty($data['source_id'])) {
                    $source = findByTypeId($data['source_type'], $data['source_id']);
                    $check = $source && method_exists($source, 'isSource') && $source->isSource() && $source->checkPermissionPost($user->id);
                } else if (! empty($data['source_type']) || ! empty($data['source_id'])) {
                    $check = false;
                }

                if (! $check)  {
                    return $validator->errors()->add('source', __('The source does not exist.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.'),
            'content.required' => __('The content is required.'),
            'photos.required' => __('Photos is required.'),
            'parent_id.required' => __('Parent is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => getMaxTextSql(setting('feature.post_character_max'))]),
            'comment_privacy.required' => __('Comment privacy is required.'),
            'comment_privacy.in' => __('Comment privacy is not in list.'),
            'subject_type.required' => __('The subject type is required.'),
            'subject_id.required' => __('The subject id is required.'),
            'close_minute.min' => __('The close minute must be at least 1.'),
            'paid_type.required' => __('The paid type is required.'),
            'paid_type.in' => __('The paid type is not in list.'),
            'thumb_file_id.required' => __('The thumbnail is required.'),
            'content_amount.required' => __('The amount is required.'),
            'content_amount.min' => __('The amount must be at least 1.'),
        ];
    }
}
