<?php


namespace Packages\ShaunSocial\Dating\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Traits\Utility;

class DatingSwipeRequest extends BaseFormRequest
{
    use Utility;

    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails() && $this->action == 'like') {
            $validator->after(function ($validator) {
                $user = $this->user();

                $this->checkPermissionActionLog(
                    'dating.maximum_number_of_right_swipes',
                    'dating_swipe',
                    $user
                );
            });
        }
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
