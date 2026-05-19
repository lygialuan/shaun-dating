<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreStopCommentValidate extends BaseFormRequest
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

                    if (! $post->canStopComment($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot stop comment this post.'));
                    }

                },
            ],
            'stop' => 'required|boolean',
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required'),
            'stop.required' => __('The stop comment is required.'),
        ];
    }
}
