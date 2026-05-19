<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;

class GetPostValidate extends BaseFormRequest
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
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $post) {
                        return $fail(__('The post is not found.'));
                    }

                    if (! $post->canView($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view this post.'));
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
