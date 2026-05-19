<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class CheckPasswordValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.')
        ];
    }
}
