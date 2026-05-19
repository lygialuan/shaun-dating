<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupRule;
use Packages\ShaunSocial\Group\Models\GroupMember;

class DeleteRuleValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();

                    $rule = GroupRule::findByField('id', $id);
                    if (! $rule) {
                        return $fail(__('The rule is not found.'));
                    }

                    if (! GroupMember::getAdmin($viewer->id, $rule->group_id)) {
                        return $fail(__('The rule is not found.'));
                    }
                },
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
