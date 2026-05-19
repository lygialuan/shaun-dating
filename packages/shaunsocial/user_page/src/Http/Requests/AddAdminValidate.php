<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class AddAdminValidate extends PageValidate
{
    public function authorize()
    {
        return $this->user()->isPageOwner();
    }

    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $user = User::findByField('id', $id);
    
                    if (! $user || $user->isPage()) {
                        return $fail(__('The user is not found.'));
                    }

                    if ($viewer->getAdminPage($id)) {
                        return $fail(__('This user is already an administrator.'));
                    } 
                },
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
