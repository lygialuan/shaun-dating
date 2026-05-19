<?php


namespace Packages\ShaunSocial\Core\Http\Requests\UserList;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserListMember;

class DeleteMemberValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $member = UserListMember::findByField('id', $id);
                    if (! $member || ! $member->getUserList()->isOwner($viewer->id)) {
                        return $fail(__('The member is not found.'));
                    }
                }
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
