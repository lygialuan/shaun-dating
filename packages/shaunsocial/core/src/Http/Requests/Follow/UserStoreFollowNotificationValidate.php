<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Follow;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class UserStoreFollowNotificationValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|alpha_num',
            'action' => 'required|string|in:add,remove',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $viewer = $this->user();
                $user = User::findByField('id', $data['id']);

                if (! $user) {
                    return $validator->errors()->add('user', __('The user is not found.'));
                }
                $follow = $viewer->getFollow($user->id);
                if (! $follow) {
                    return $validator->errors()->add('user', __('You must follow this user first.'));
                }

                switch ($data['action']) {
                    case 'add':
                        if ($follow->enable_notify) {
                            return $validator->errors()->add('user', __("You've already enabled receiving follow notifications from this user."));
                        }
                        break;
                    case 'remove':
                        if (! $follow->enable_notify) {
                            return $validator->errors()->add('user', __("You've already disabled receiving follow notifications from this user."));
                        }
                        break;
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'id.required' => __('The user id is required.'),
            'action.required' => __('The action is required.'),
            'action.in' => __('The action is not in the list (add,remove).'),
        ];
    }
}
