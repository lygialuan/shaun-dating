<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class RemoveValidate extends UserValidate
{
    public function authorize()
    {
        $user = $this->user();
        $userTwoFactor = UserTwoFactor::getByUser($user->id, true);
        return $userTwoFactor;
    }

    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ]
        ];
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.')
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
