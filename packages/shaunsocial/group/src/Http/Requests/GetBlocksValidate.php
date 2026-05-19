<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class GetBlocksValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'query' => 'nullable|string',
            'page' => 'integer'
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'page.integer' => __('The page must number.')
        ];
    }
}
