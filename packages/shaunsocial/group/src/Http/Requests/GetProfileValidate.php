<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Enum\GroupStatus;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Models\GroupBlock;

class GetProfileValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    $group = Group::findByField('id', $id);
                    if (! $group) {
                        return $fail(__('The group is not found.'));
                    }

                    if (! $group->checkStatus()) {
                        return $fail(__('The group is not found.'));
                    }

                    if (!$isAdmin && GroupBlock::getBlock($viewerId, $id)) {
                        return $fail(__('The group is not found.'));
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
