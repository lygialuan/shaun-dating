<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Validation\GroupOwnerValidate;

class StoreOpenValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupOwnerValidate(),
                function ($attribute, $id, $fail) {
                    $group = Group::findByField('id', $id);
                    if ($group && ! $group->canOpen()) {
                        return $fail(__('The group is not found.'));
                    }
                }
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'password.required' => __('The password is required.')
        ];
    }
}
