<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StoreNameValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 255 characters.'),
        ];
    }
}
