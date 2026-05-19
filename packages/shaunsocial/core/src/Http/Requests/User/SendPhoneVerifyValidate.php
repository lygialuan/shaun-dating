<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\PhoneVerifyValidate;
use Packages\ShaunSocial\Core\Traits\Utility;

class SendPhoneVerifyValidate extends PhoneVerifyValidate
{
    use Utility;

    public function rules()
    {        
        return [];
    }
    
    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                if (setting('spam.send_sms_enable_recapcha')) {
                    $result = $this->validateSpam($this->request->all());
                    if (! $result['status']) {
                        throw new MessageHttpException($result['message']); 
                    }
                }
                
                $viewer = $this->user();
                if (! $viewer->phone_number) {
                    return $validator->errors()->add('user', __('Please input phone number.'));
                }
                if ($viewer->phone_verified) {
                    return $validator->errors()->add('user', __('You have verified your phone.'));
                }
            });
        }

        return $validator;
    }
}
