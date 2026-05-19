<?php
namespace Packages\ShaunSocial\Core\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Models\UserTwoFactor;

class TwoFactValidate extends UserValidate
{
    public function authorize()
    {
        if (parent::authorize()) {
            $user = $this->user();
            $userTwoFactor = UserTwoFactor::getByUser($user->id, true);
            return ! $userTwoFactor;
        }
        
        return false;
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
