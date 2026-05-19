<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StoreDescriptionValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'description' => 'string|nullable|max:1024',
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'description.max' => __('The description must not be greater than 1024 characters.'),
        ];
    }
}
