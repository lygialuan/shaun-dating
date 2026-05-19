<?php


namespace Packages\ShaunSocial\Core\Http\Requests\TwoFactor;

use Packages\ShaunSocial\Core\Exceptions\UserActiveHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;
use Packages\ShaunSocial\Core\Traits\Utility;

class SendLoginCodeValidate extends BaseFormRequest
{
    use Utility;

    public function rules()
    {
        return [
            'two_factor_code' => [
                'required',
                function ($attribute, $code, $fail) {
                    if ($code) {
                        $result = $this->checkCodeVerify(0, 'two_factory_code', $code, '', config('shaun_core.validation.two_factor_verify'));
                        if (! $result['status']) {
                            return $fail($result['message']);
                        }
                        $codeVerify = $result['codeVerify'];
                        $userTwoFactor = UserTwoFactor::getByUser($codeVerify->user_id, true);
                        if (! $userTwoFactor) {
                            return $fail(__('Do not support this method.'));
                        }
                        $viewer = $userTwoFactor->getUser();
                        if (! $viewer->is_active) {
                            throw new UserActiveHttpException(400);
                        }
                    }                    
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'two_factor_code.required' => __('The two-factor code is required.')
        ];
    }
}
