<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Models\GroupPostPending;

class DeletePostPendingValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $postPending = GroupPostPending::findByField('id', $id);
                    if (! $postPending) {
                        return $fail(__('The post is not found.'));
                    }

                    if (! GroupMember::getAdmin($viewer->id, $postPending->group_id)) {
                        return $fail(__('The post is not found.'));
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
