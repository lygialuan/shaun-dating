<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Follow;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class UserGetMyFollowerValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'type' => 'nullable|in:user,page',
        ];
    }

    public function messages()
    {
        return [
            'type.in' => __('The type is not in the list (user,page).'),
        ];
    }
}
