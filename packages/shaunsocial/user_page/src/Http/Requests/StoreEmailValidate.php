<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

class StoreEmailValidate extends PageValidate
{
    public function rules()
    {
        return [
            'email' => 'string|nullable|email|max:255'
        ];
    }

    public function messages()
    {
        return [
            'email.max' => __('The email must not be greater than 255 characters.'),
            'email.email' => __('The email must be a valid email address.'),
        ];
    }
}
