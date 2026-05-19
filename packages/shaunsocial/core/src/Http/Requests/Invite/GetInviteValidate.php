<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Invite;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Enum\InviteType;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetInviteValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'nullable',
            'type' => ['required', Rule::in(InviteType::values())],
            'page' => 'integer'
        ];
    }
    
    public function messages()
    {
        return [
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'page.integer' => __('The page must number.')
        ];
    }
}
