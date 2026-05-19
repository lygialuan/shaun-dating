<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;

class DeleteUserValidate extends BaseFormRequest
{
    public function authorize()
    {
        return $this->user()->canDelete() && setting('user.allow_delete');
    }

    public function rules()
    {
        return [
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'password.required' => __('The password is required.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You can not delete your account.'));
    }
}
