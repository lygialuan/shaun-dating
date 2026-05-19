<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserFcmToken;

class StoreFcmTokenValidate extends BaseFormRequest
{
    public function rules()
    {
        $types = UserFcmToken::getTypes();

        return [
            'token' => 'required|string|max:255',
            'type' => ['required', Rule::in($types)],
        ];
    }

    public function messages()
    {
        return [
            'token.required' => __('The token is required.'),
            'token.max' => __('The token must not be greater than 255 characters.'),
            'type.required' => __('Type is required.'),
            'type.in' => __('Type is not in list.'),
        ];
    }
}
