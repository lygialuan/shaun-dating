<?php
namespace Packages\ShaunSocial\Core\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;

class PhoneVerifyValidate extends UserValidate
{
    public function authorize()
    {
        if (parent::authorize()) {
            return setting('feature.phone_verify') && ! $this->user()->isModerator();
        }
        
        return false;
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
