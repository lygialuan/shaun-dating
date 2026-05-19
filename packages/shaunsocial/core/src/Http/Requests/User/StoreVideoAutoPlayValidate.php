<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreVideoAutoPlayValidate extends BaseFormRequest
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
