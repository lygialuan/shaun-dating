<?php


namespace Packages\ShaunSocial\Core\Http\Requests\UserList;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserList;

class StoreMembersValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $list = UserList::findByField('id', $id);
                    if (! $list || ! $list->isOwner($viewer->id)) {
                        return $fail(__('The user list is not found.'));
                    }
                }
            ],
            'user_ids' => [
                'required',
                function ($attribute, $userIds, $fail) {
                    if (! is_array($userIds)) {
                        return $fail(__('The user ids is required.'));
                    }

                    if (! count($userIds)) {
                        return $fail(__('The user ids is required.'));
                    }

                    if (count($userIds) > config('shaun_core.core.limit_save_auto')) {
                        return $fail(__('Number of user that can be add per time is :1.', ['1' => config('shaun_core.core.limit_save_auto')]));
                    }

                    $viewer = $this->user();

                    foreach ($userIds as $id) {
                        if ($id == $viewer->id) {
                            return $fail(__('You cannot add themselves.'));
                        }
                        $user = User::findByField('id', $id);
                        if (! $user) {
                            return $fail(__('The user is not found.'));
                        }
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'user_ids.required' => __('The user ids is required.'),
        ];
    }
}
