<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\Utility;

class EditPostValidate extends BaseFormRequest
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

                    if (! $post->canEdit($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot edit this post.'));
                    }

                },
            ],
            'content' => ['string', 'nullable']
        ];

        $rules['content'][] = 'max:'.getMaxTextSql(setting('feature.post_character_max'));

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                $data = $validator->getData();
                $post = Post::findByField('id', $data['id']);

                if ($post->type == 'text' && empty($data['content'])) {
                    return $validator->errors()->add('content', __('The content is required.'));
                }
                $content = $post->getMentionContent(null);
                if ($data['content'] == $content) {
                    return $validator->errors()->add('content', __('The content must be different.'));
                }

                $this->checkPermissionHaveValue('post.character_max', strlen($data['content']), $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => getMaxTextSql(setting('feature.post_character_max'))]),
        ];
    }
}
