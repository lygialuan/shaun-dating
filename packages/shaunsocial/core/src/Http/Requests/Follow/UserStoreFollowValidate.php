<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Follow;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class UserStoreFollowValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => 'required|alpha_num',
            'action' => 'required|string|in:follow,unfollow',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $viewer = $this->user();
                $isAdmin = $this->user()->isModerator();
                $user = User::findByField('id', $data['id']);

                if ($data['id'] == $viewer->id) {
                    return $validator->errors()->add('user', __('You cannot follow themselves.'));
                }

                if (! $user) {
                    return $validator->errors()->add('user', __('The user is not found.'));
                }

                if (! $user->canFollow($viewer->id) && !$isAdmin) {
                    return $validator->errors()->add('user', __('You cannot follow this user.'));
                }

                switch ($data['action']) {
                    case 'follow':
                        if ($viewer->getFollow($data['id'])) {
                            return $validator->errors()->add('user', __("You've already followed this user."));
                        }
                        break;
                    case 'unfollow':
                        if (! $viewer->getFollow($data['id'])) {
                            return $validator->errors()->add('user', __("You've already unfollowed this user."));
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
            'action.in' => __('The action is not in the list (follow,unfollow).'),
        ];
    }
}
