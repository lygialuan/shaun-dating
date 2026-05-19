<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Follow;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class UserGetFollowerValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $userId, $fail) use ($viewer) {
                    $isAdmin = $viewer ? $viewer->isModerator() : false;
                    $viewerId = $viewer ? $viewer->id : 0;
                    $user = User::findByField('id', $userId);

                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }

                    if (! $user->canViewFollower($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view follower this user.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The user id is required.'),
        ];
    }
}
