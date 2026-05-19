<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

class StoreEnableReviewValidate extends PageValidate
{
    public function rules()
    {
        return [
            'enable' => 'required|boolean'
        ];
    }

    public function messages()
    {
        return [
            'enable.required' => __('The enable is required.'),
        ];
    }
}
