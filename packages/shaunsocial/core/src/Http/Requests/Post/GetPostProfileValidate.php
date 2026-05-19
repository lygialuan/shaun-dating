<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Post;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class GetPostProfileValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $userId, $fail) use ($viewer) {
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    $user = User::findByField('id', $userId);

                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }

                    if (! $user->canViewProfilePost($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view post this user.'));
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
