<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;

class GetNewHomeValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'alpha_num',
                function ($attribute, $postId, $fail) use ($viewer) {
                    if ($postId) {
                        $post = Post::findByField('id', $postId);
                        $viewerId = $viewer ? $viewer->id : 0;
                        $isAdmin = $viewer ? $viewer->isModerator() : false;

                        if (! $post) {
                            return $fail(__('The post is not found.'));
                        }

                        if (! $post->canView($viewerId) && !$isAdmin) {
                            return $fail(__('You cannot view this post.'));
                        }
                    }
                    
                },
            ],
        ];
    }
}
