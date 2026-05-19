<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\PhoneVerifyValidate;
use Packages\ShaunSocial\Core\Traits\Utility;

class CheckPhoneVerifyValidate extends PhoneVerifyValidate
{
    use Utility;

    public function rules()
    {        
        $viewer = $this->user();

        return [
            'code' => [
                'required',
                'string',
                function ($attribute, $code, $fail) use ($viewer) {
                    if ($code) {
                        $result = $this->checkCodeVerify($viewer->id, 'phone_verify', $code, $viewer->phone_number, config('shaun_core.validation.phone_verify'));
                        if (! $result['status']) {
                            return $fail($result['message']);
                        }
                    }                    
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'code.required' => __('The code is required.'),
        ];
    }
}
