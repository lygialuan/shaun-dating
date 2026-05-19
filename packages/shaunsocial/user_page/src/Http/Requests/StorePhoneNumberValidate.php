<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

class StorePhoneNumberValidate extends PageValidate
{
    public function rules()
    {
        return [
            'phone_number' => 'string|nullable|max:15|regex:'.config('shaun_core.regex.phone_number')
        ];
    }

    public function messages()
    {
        return [
            'phone_number.max' => __('The phone number must not be greater than 15 characters.'),
            'phone_number.regex' => __('The phone number must be a valid.'),
        ];
    }
}
