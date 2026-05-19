<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupRule;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StoreRuleValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'nullable',
                function ($attribute, $id, $fail) {
                    if ($id) {
                        $viewer = $this->user();

                        $rule = GroupRule::findByField('id', $id);
                        if (! $rule) {
                            return $fail(__('The rule is not found.'));
                        }

                        if (! GroupMember::getAdmin($viewer->id, $rule->group_id)) {
                            return $fail(__('The rule is not found.'));
                        }
                    }
                },
            ],
            'group_id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:1024',
        ];
    }

    public function messages()
    {
        return [
            'group_id.required' => __('The group id is required.'),
            'title.required' => __('The title is required.'),
            'title.max' => __('The title must not be greater than 255 characters.'),
            'description.required' => __('The description is required.'),
            'description.max' => __('The description must not be greater than 1024 characters.'),
        ];
    }
}
