<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class SwitchPageValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' =>  [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    if ($viewer->isPage()) {
                        $viewer = $viewer->getParent();
                    }
                    $user = User::findByField('id', $id);

                    if (! $user) {
                        return $fail(__('The page is not found.'));
                    }

                    if (! $user->is_active) {
                        if ($user->isPage()) {
                            return $fail(__('The page is pending approval.'));
                        } else {
                            return $fail(__('The user is pending approval.'));
                        }
                    }

                    if ($user->isPage() && ! $user->getAdminPage($viewer->id)) {
                        return $fail(__('The page is not found.'));
                    }

                    if (! $user->isPage() && $viewer->id != $user->id) {
                        return $fail(__('The user is not found.'));
                    }
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}