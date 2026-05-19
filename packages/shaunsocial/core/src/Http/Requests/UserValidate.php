<?php
namespace Packages\ShaunSocial\Core\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;

class UserValidate extends BaseFormRequest
{
    public function authorize()
    {
        $viewer = $this->user();
        if ($viewer && $viewer->isPage()) {
            return false;
        }

        return true;
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
