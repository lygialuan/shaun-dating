<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Enum\CommentPrivacy;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Traits\Utility;

class EditCommentPrivacyValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {
        $commentPrivacies = CommentPrivacy::values();
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

                    if (! $post->canChangeCommentPrivacy($viewer->id)) {
                        return $fail(__('You cannot edit comment privacy this post.'));
                    }
                },
            ],
            'comment_privacy' => ['required', Rule::in($commentPrivacies)],
        ];
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required'),
            'comment_privacy.required' => __('Comment privacy is required.'),
            'comment_privacy.in' => __('Comment privacy is not in list.'),
        ];
    }
}
