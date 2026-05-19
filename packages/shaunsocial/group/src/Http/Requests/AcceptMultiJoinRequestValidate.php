<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupMemberRequest;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class AcceptMultiJoinRequestValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'request_ids' => [
                'required',
                function ($attribute, $requestIds, $fail) {
                    if (! is_array($requestIds)) {
                        return $fail(__('The request ids are not in the list.'));
                    }
                }
            ]
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $groupId = $data['id'];
                $requestIds = $data['request_ids'];
                foreach ($requestIds as $id) {
                    $request = GroupMemberRequest::findByField('id', $id);
                    if (! $request || $request->group_id != $groupId) {
                        return $validator->errors()->add('request_ids', __('The request is not found.'));
                    }
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'request_ids.required' => __('The request ids is required.')
        ];
    }
}
