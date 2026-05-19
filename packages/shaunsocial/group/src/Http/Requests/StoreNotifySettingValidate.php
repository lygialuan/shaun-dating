<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupMember;

class StoreNotifySettingValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        $rules = [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $group = Group::findByField('id', $id);
                    if (! $group) {
                        return $fail(__('The group is not found.'));
                    }

                    $member = GroupMember::getMember($viewer->id, $id);
                    if (! $member) {
                        return $fail(__("You are not a member of the group."));
                    }
                }
            ]
        ];
        if ($this->input('id')) {
            $group = Group::findByField('id', $this->input('id'));
            if ($group) {
                $member = GroupMember::getMember($viewer->id, $group->id);
                if ($member) {
                    $keys = array_keys($member->getNotifySetting());
                    foreach ($keys as $key) {
                        $rules[$key] = 'required|boolean';
                    }
                }
            }
        }
        
        return $rules;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
