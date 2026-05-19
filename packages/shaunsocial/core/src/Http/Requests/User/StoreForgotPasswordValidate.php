<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PasswordValidation;

class StoreForgotPasswordValidate extends BaseFormRequest
{
    use Utility;
    
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
            'code' => [
                'required'
            ],
            'password' => ['required',new PasswordValidation()],
            'password_confirmed' => ['required'],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $user = User::findByField('email', $data['email']);

                $result = $this->checkCodeVerify($user->id, 'forgot_password', $data['code']);
                if (! $result['status']) {
                    return $validator->errors()->add('code', $result['message']);
                }

                if ($data['password'] != $data['password_confirmed']) {
                    return $validator->errors()->add('password_confirmed', __('The confirm password does not match.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'email.required' => __('The email is required.'),
            'email.email' => __('The email must be a valid email address.'),
            'code.required' => __('The code is required.'),
            'password.required' => __('The password is required.'),
            'password_confirmed.required' => __('The confirm password is required.')
        ];
    }
}
