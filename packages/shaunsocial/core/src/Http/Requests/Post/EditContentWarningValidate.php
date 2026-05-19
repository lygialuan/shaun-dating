<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Models\ContentWarningCategory;

class EditContentWarningValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $viewer = $this->user();

        $rules = [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $postId, $fail) use ($viewer) {
                    $post = Post::findByField('id', $postId);
                    $isAdmin = $viewer->isModerator();

                    if (! $post) {
                        return $fail(__('The post is not found.'));
                    }

                    if (! $post->canChangeContentWarning($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot change content warning of this post.'));
                    }

                },
            ],
            'content_warning_categories' => ['nullable']
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $post = Post::findByField('id', $data['id']);

                if(in_array($post->type, ['photo', 'video', 'vibb'])){
                    if (!$data['content_warning_categories']) {
                        return;
                    }

                    if (!is_array($data['content_warning_categories'])) {
                        return $validator->errors()->add('content_warning_categories', __('The content warning category is not in the list.'));
                    }
                    
                    $check = true;
                    foreach ($data['content_warning_categories'] as $id) {
                        if ($id) {
                            $category = ContentWarningCategory::findByField('id' ,$id);
                            if (!$category || $category->isDeleted()) {
                                $check = false;
                            }
                        }
                    }

                    if (!$check) {
                        return $validator->errors()->add('content_warning_categories', __('The content warning category is not found.'));
                    }
                } else {
                    return $validator->errors()->add('content_warning_categories', __('The post does not support warning content.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required')
        ];
    }
}
