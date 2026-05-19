<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Models\User;

class StorePagePrivacyValidate extends PageValidate
{
    public function rules()
    {
        $viewer = $this->user();
        return [
            'type' => ['required', Rule::in(array_keys($viewer->getPrivacyPageInfoSetting()))],
            'value' => ['required', Rule::in(array_keys(User::getPrivacyList()))]
        ];
    }

    public function messages()
    {
        return [
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'value.required' => __('The value is required.'),
            'value.in' => __('The type is not in the list.')
        ];
    }
}
