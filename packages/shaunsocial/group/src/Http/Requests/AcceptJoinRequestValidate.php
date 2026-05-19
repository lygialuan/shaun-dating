<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;

class AcceptJoinRequestValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $request = GroupMemberRequest::findByField('id', $id);
                    if (! $request) {
                        return $fail(__('The request is not found.'));
                    }

                    $group = $request->getGroup();
                    if (! $group) {
                        return $fail(__('The request is not found.'));
                    }

                    $groupMember = GroupMember::getAdmin($viewer->id, $group->id);
                    if (! $groupMember) {
                        return $fail(__('The request is not found.'));
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
