<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class StorePrivacySettingValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();
        $keys = array_keys($viewer->getPrivacySetting());
        $rules = [];

        foreach ($keys as $key) {
            $rules[$key] = 'required|boolean';
        }
        $privacyList = User::getPrivacyList();
        $rules['privacy'] = ['required', Rule::in(array_keys($privacyList))];
        $chatPrivacyList = User::getChatPrivacyList();
        $rules['chat_privacy'] = ['required', Rule::in(array_keys($chatPrivacyList))];
        
        return $rules;
    }
}
