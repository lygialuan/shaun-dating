<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;

class StoreJoinValidate extends BaseFormRequest
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

                    if (! $group->canJoin($viewer->id)) {
                        return $fail(__("You can't join this group."));
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
