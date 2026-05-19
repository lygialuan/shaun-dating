<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupMember;

class RemoveMemberValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $member = GroupMember::findByField('id', $id);
                    if (! $member) {
                        return $fail(__('The member is not found.'));
                    }

                    if ($member->user_id == $viewer->id) {
                        return $fail(__('You cannot remove yourself.'));
                    }

                    $group = $member->getGroup();
                    if (! $group || ! GroupMember::getAdmin($viewer->id, $group->id)) {
                        return $fail(__('The member is not found.'));
                    }

                    if ($member->isAdmin()) {
                        if (! GroupMember::checkOwner($viewer->id, $group->id)) {
                            return $fail(__('Only owner can remove this user.'));
                        }
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
