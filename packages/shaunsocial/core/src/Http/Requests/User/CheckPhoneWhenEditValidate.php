<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use App\Models\User;
use Packages\ShaunSocial\Core\Http\Requests\PhoneVerifyValidate;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Core\Validation\PhoneValidation;

class CheckPhoneWhenEditValidate extends PhoneVerifyValidate
{
    use Utility;

    public function rules()
    {
        return [
            'code' => [
                'required',
                'string'
            ],
            'phone_number' => [
                'required', 
                new PhoneValidation(),
                function ($attribute, $phoneNumber, $fail) {
                    if ($phoneNumber) {
                        $user = User::checkExistPhoneNumber($phoneNumber);
                        if ($user) {
                            return $fail(__('The phone number already exists.'));
                        }
                    }
                },
            ]
        ];
    }

    public function prepareForValidation()
    {
        parent::prepareForValidation();
        
        $phoneNumber = $this->input('phone_number');
        if ($phoneNumber) {
            $this->merge([
                'phone_number' => str_replace(' ', '', $phoneNumber)
            ]);
        }
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {                
                $viewer = $this->user();
                if (! $viewer->phone_verified) {
                    return $validator->errors()->add('user', __('You do not have verify your phone.'));
                }
                $data = $validator->getData();
                $result = $this->checkCodeVerify($viewer->id, 'phone_verify', $data['code'], $data['phone_number'], config('shaun_core.validation.phone_verify'));
                if (! $result['status']) {
                    return $validator->errors()->add('code', $result['message']);
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'code.required' => __('The code is required.'),
        ];
    }
}
