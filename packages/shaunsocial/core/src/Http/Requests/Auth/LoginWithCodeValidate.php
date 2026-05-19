<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Auth;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;

class LoginWithCodeValidate extends BaseFormRequest
{
    use Utility;
    
    public function rules()
    {        return [
            'code' => [
                'required',
                'string',
                function ($attribute, $code, $fail) {
                    if ($code) {
                        $result = $this->checkCodeVerify(0, 'login', $code);
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
            'code.required' => __('The code is required.')
        ];
    }
}
