<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetBlockValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'type' => 'nullable|in:all,user,page',
        ];
    }

    public function messages()
    {
        return [
            'type.in' => __('The type is not in the list (all,user,page).'),
        ];
    }
}