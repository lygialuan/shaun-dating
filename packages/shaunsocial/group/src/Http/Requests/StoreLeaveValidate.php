<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupMember;

class StoreLeaveValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
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

                    if ($member->isOwner()) {
                        return $fail(__("You can't leave this group."));
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
