<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StorePinValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'post_id' => [
                'required',
                function ($attribute, $postId, $fail) {
                    $post = Post::findByField('id', $postId);
                    if (! $post) {
                        return $fail(__('The post is not found.'));
                    }
                }
            ],
            'action' => 'required|string|in:pin,unpin',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $viewer = $this->user();
                $post = Post::findByField('id', $data['post_id']);
                $group = Group::findByField('id', $data['id']);
                
                if (! $post->isSourceOwner($group)) {
                    return $validator->errors()->add('post_id', __('The post is not found.'));
                }

                switch ($data['action']) {
                    case 'pin':
                        if ($post->pin_date) {
                            return $validator->errors()->add('post_id', __("This post is already pinned."));
                        }
                        break;
                    case 'unpin':
                        if (! $post->pin_date) {
                            return $validator->errors()->add('post_id', __("This post is not pinned."));
                        }
                        break;
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'post_id.required' => __('The post id is required.'),
            'action.required' => __('The action is required.'),
            'action.in' => __('The action is not in the list (pin,unpin).'),
        ];
    }
}
