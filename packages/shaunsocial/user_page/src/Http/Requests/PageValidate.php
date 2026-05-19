<?php
namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class PageValidate extends BaseFormRequest
{
    public function authorize()
    {
        $viewer = $this->user();
        if ($viewer && ! $viewer->isPage()) {
            return false;
        }

        return true;
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
