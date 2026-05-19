<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;

class DeletePostValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $postId, $fail) use ($viewer) {
                    $post = Post::findByField('id', $postId);
                    $isAdmin = $viewer->isModerator();

                    if (! $post) {
                        return $fail(__('The post is not found.'));
                    }

                    if (! $post->canDelete($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot delete this post.'));
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
