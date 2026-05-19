<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Group\Validation\GroupOwnerValidate;

class DeleteValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupOwnerValidate(),
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'password.required' => __('The password is required.')
        ];
    }
}
