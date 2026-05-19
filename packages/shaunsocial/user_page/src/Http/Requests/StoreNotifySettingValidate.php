<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

class StoreNotifySettingValidate extends PageValidate
{
    public function rules()
    {
        $viewer = $this->user();
        $adminPage = $viewer->getPageAdminCurrentlyLogin();
        $keys = array_keys($adminPage->getNotifySetting());
        $rules = [];

        foreach ($keys as $key) {
            $rules[$key] = 'required|boolean';
        }
        
        return $rules;
    }
}
