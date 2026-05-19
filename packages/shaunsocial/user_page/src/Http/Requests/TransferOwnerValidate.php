<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class TransferOwnerValidate extends PageValidate
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

                    if ($id == $viewer->getParent()->id) {
                        return $fail(__('You cannot transfer to yourself.'));
                    }

                    $user = User::findByField('id', $id);
    
                    if (! $user || $user->isPage()) {
                        return $fail(__('The user is not found.'));
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
            'password.required' => __('The password is required.')
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
