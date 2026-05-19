<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Enum\GroupMemberRole;
use Packages\ShaunSocial\Group\Models\GroupMember;

class RemoveAdminValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();

                    $member = GroupMember::findByField('id', $id);
    
                    if (! $member || $member->role != GroupMemberRole::ADMIN) {
                        return $fail(__('The user is not found.'));
                    }

                    if ($viewer->id != $member->user_id) {
                        if (! GroupMember::checkOwner($viewer->id, $member->group_id)) {
                            return $fail(__('The user is not found.'));
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
