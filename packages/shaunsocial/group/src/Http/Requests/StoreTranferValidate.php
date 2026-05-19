<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Group\Models\GroupBlock;
use Packages\ShaunSocial\Group\Validation\GroupOwnerValidate;

class StoreTranferValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupOwnerValidate(),
            ],
            'user_id' => [
                'required',
                function ($attribute, $userId, $fail) {
                    $viewer = $this->user();
                    if ($userId == $viewer->id) {
                        return $fail(__('You cannot transfer to yourself.'));
                    }

                    $user = User::findByField('id', $userId);
    
                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }
                }
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                
                $block = GroupBlock::getBlock($data['user_id'], $data['id']);
                if ($block) {
                    return $validator->errors()->add('user_id', __('You cannot transfer group to user who has been blocked by you.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'user_id.required' => __('The user id is required.'),
            'password.required' => __('The password is required.')
        ];
    }
}
