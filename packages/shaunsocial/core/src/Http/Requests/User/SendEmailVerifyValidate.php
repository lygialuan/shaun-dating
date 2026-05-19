<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\UserValidate;

class SendEmailVerifyValidate extends UserValidate
{
    public function rules()
    {        
        return [];
    }
    
    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();

                if ($viewer->email_verified) {
                    return $validator->errors()->add('user', __('You have verified your email.'));
                }
            });
        }

        return $validator;
    }
}
