<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreNotificationSettingValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();
        $keys = array_keys($viewer->getNotifySetting());
        $keys[] = 'enable_notify';
        $rules = [];

        foreach ($keys as $key) {
            $rules[$key] = 'required|boolean';
        }
        
        return $rules;
    }
}
