<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Requests;

use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class StoreAdvertisingValidate extends ValidateStoreAdvertisingValidate
{
    public function rules()
    {
        $rules = parent::rules();
        $rules['password'] = [
            'required',
            new PasswordVerifyValidation(),
        ];

        return $rules;
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.')
        ];
    }
}
