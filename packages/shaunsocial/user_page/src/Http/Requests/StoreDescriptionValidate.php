<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

class StoreDescriptionValidate extends PageValidate
{
    public function rules()
    {
        return [
            'description' => 'string|nullable|max:255'
        ];
    }

    public function messages()
    {
        return [
            'description.max' => __('The description must not be greater than 255 characters.'),
        ];
    }
}
