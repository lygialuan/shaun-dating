<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Post;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class StorePaidPostValidate extends BaseFormRequest
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
                    
                    if (! $post || ! $post->canPaid($viewer->id)) {
                        return $fail(__('The post is not found.')); 
                    }
                },
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
            'ref_code' => [
                'nullable'
            ]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'password.required' => __('The password is required.'),
        ];
    }
}
