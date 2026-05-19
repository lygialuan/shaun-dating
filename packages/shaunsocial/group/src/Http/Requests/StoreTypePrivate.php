<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Enum\GroupType;
use Packages\ShaunSocial\Group\Models\Group;
use Packages\ShaunSocial\Group\Validation\GroupOwnerValidate;

class StoreTypePrivate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupOwnerValidate(),
            ]
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $group = Group::findByField('id', $data['id']);
                if ($group->type == GroupType::PRIVATE) {
                    $validator->errors()->add('end', __('You can not change this group to private.'));
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
