<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\UserNameValidate;

class GetUserProfileValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'user_name' => [
                'required',
                'string',
                new UserNameValidate(),
                'max:128',
                function ($attribute, $userName, $fail) {
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;
                    $user = User::findByField('user_name', $userName);

                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }

                    if (! $user->canViewProfile($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view this user.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'user_name.required' => __('The username is required.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
        ];
    }
}
