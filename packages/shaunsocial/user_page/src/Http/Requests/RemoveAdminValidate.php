<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Models\User;

class RemoveAdminValidate extends PageValidate
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();

                    $user = User::findByField('id', $id);
    
                    if (! $user) {
                        return $fail(__('The user is not found.'));
                    }

                    if (! $viewer->getAdminPage($id)) {
                        return $fail(__('This user is not admin.'));
                    }

                    if ($viewer->isPageOwner()) {
                        if ($viewer->getParent()->id == $id) {
                            return $fail(__('You cannot remove owner.'));
                        }
                    } else {
                        if ($viewer->getParent()->id != $id) {
                            return $fail(__('You cannot use this function.'));
                        }
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
