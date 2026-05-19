<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Group\Models\GroupBlock;
use Packages\ShaunSocial\Group\Models\GroupMember;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StoreBlockValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate()
            ],
            'user_id' => [
                'required',
                function ($attribute, $userId, $fail) {
                    $viewer = $this->user();
                    $user = User::findByField('id', $userId);
                    if (! $user) {
                        return $fail('The user is not found.');
                    }

                    if ($viewer->id == $userId) {
                        return $fail(__('You cannot block themselves.'));
                    }
                },
            ]
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $member = GroupMember::getAdmin($data['user_id'], $data['id']);
                if ($member) {
                    return $validator->errors()->add('user_id', __("You can't block admin member."));
                }

                $block = GroupBlock::getBlock($data['user_id'], $data['id']);
                if ($block) {
                    return $validator->errors()->add('user_id', __("You've already blocked this user."));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.')
        ];
    }
}
