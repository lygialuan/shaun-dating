<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class RemoveWebFcmTokenValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'token' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'token.required' => __('The token is required.'),
            'token.max' => __('The token must not be greater than 255 characters.'),
        ];
    }
}
