<?php
namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class CreatorValidate extends BaseFormRequest
{
    public function authorize()
    {
        $viewer = $this->user();
        if (! $viewer->canShowCreatorDashBoard()) {
            return false;
        }

        return true;
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
