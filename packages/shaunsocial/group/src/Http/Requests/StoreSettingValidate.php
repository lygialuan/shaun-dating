<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\SlugValidate;
use Packages\ShaunSocial\Group\Enum\GroupWhoCanPost;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StoreSettingValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'post_approve_enable' => 'required|boolean',
            'who_can_post' => ['required', Rule::in(GroupWhoCanPost::values())],
            'slug' => ['required', 'max:128', new SlugValidate()]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'post_approve_enable.required' => __('The enable is required.'),
            'who_can_post.required' => __('The who can post is required.'),
            'slug.required' => __('The slug is required.'),
            'slug.max' => __('The slug must not be greater than 128 characters.')
        ];
    }
}
