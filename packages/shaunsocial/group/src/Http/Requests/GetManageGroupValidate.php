<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Enum\GroupStatus;

class GetManageGroupValidate extends BaseFormRequest
{
    public function rules()
    {
        $status = array_keys(GroupStatus::getAll());
        $status[] = 'all';
        return [
            'status' => [
                'required',
                Rule::in($status)
            ],
            'page' => 'integer'
        ];
    }

    public function messages()
    {
        return [
            'status.required' => __('The status is required.')
        ];
    }
}
