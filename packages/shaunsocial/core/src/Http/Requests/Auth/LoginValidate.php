<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\Utility;

class LoginValidate extends BaseFormRequest
{
    use Utility;
    protected $spamValidate = true;
    
    public function rules()
    {
        return [
            'email' => ['required',
                function ($attribute, $email, $fail) {
                    $message = __('The email or username was incorrect.');
                    if (setting('feature.phone_verify')) {
                        $message = __('The email, username or phone number was incorrect.');
                    }
                    $checked = false;
                    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                        $user = User::findByField('email', $email);
                        if ($user) {
                            $checked = true;
                        }
                    } if (setting('feature.phone_verify') && validatePhoneNumber($email)) {
                        $user = User::checkExistPhoneNumber($email);
                        if ($user) {
                            $checked = true;
                        }
                    } else {
                        $user = User::findByField('user_name', $email);
                        if ($user) {
                            $checked = true;
                        }
                    }

                    if (! $checked) {
                        return $fail($message);
                    }
                },
            ],
            'password' => 'required|string',
        ];
    }

    public function withValidator($validator)
    {
        if ($this->spamValidate && setting('spam.login_enable_recapcha')) {
            if (! $validator->fails()) {
                $validator->after(function ($validator) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                });
            }
        }

        return $validator;
    }

    public function messages()
    {
        $message = __('The email or username is required.');
        if (setting('feature.phone_verify')) {
            $message = __('The email, username or phone number is required.');
        }
        return [
            'email.required' => $message,
            'password.required' => __('The password is required.'),
        ];
    }
}
