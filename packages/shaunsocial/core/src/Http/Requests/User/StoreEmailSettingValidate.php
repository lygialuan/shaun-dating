<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;

class StoreEmailSettingValidate extends UserValidate
{
    public function rules()
    {
        return [
            'email_enable' => 'required|boolean',
            'daily_email_enable' => 'required|boolean',
        ];
    }

    public function messages()
    {
        return [
            'email_enable.required' => __('The email activation is required.'),
            'daily_email_enable.required' => __('The daily email activation is required.'),
        ];
    }
}
