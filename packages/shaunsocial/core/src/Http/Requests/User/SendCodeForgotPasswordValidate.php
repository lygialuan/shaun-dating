<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class SendCodeForgotPasswordValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'email' => [
                'required',
                'email',
                function ($attribute, $email, $fail) {
                    $user = User::findByField('email', $email);
                    if (! $user || $user->isPage()) {
                        return $fail(__("The email does not exist."));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'email.required' => __('The email is required.'),
            'email.email' => __('The email must be a valid email address.'),
        ];
    }
}
