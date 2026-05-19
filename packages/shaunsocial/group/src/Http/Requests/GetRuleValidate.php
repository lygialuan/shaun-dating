<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;

class GetRuleValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $group = Group::findByField('id', $id);
                    if (! $group) {
                        return $fail(__('The group is not found.'));
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.')
        ];
    }
}
