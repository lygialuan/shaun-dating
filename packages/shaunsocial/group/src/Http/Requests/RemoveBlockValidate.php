<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupBlock;
use Packages\ShaunSocial\Group\Models\GroupMember;

class RemoveBlockValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $block = GroupBlock::findByField('id', $id);
                    if (! $block) {
                        return $fail(__('The block is not found.'));
                    }

                    $group = $block->getGroup();
                    if (!$group || ! GroupMember::getAdmin($viewer->id, $group->id)) {
                        return $fail(__('The block is not found.'));
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
