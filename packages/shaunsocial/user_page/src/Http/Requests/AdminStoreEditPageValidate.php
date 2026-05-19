<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Packages\ShaunSocial\UserPage\Validation\PageAliasValidate;

class AdminStoreEditPageValidate extends FormRequest
{
    public function rules()
    {
        return [
            'name' => 'required|string|max:64',
            'user_name' => [
                'required',
                'string',
                'max:128',
                new PageAliasValidate(),
                'unique:users,user_name,'.$this->get('id')
            ]
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'user_name.required' => __('The username is required.'),
            'user_name.unique' => __('The username has already been taken.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
        ];
    }
}
