<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;

class StorePinHomeValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $post = Post::findByField('id', $id);
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
                $post = Post::findByField('id', $data['id']);
                switch ($data['action']) {
                    case 'pin':
                        if (! $post->canPinHome($viewer)) {
                            return $validator->errors()->add('id', __("The post is not found."));
                        }
                        break;
                    case 'unpin':
                        if (! $post->canUnPinHome($viewer)) {
                            return $validator->errors()->add('id', __("The post is not found."));
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
