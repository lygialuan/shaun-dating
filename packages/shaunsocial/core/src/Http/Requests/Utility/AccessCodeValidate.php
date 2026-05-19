<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class AccessCodeValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'access_code' => [
                'required',
                'string',
                function ($attribute, $accessCode, $fail) {
                    if ($accessCode != setting('site.offline_code')) {
                        return $fail(__('The Access code is invalid.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'access_code.required' => __('The access code is required.'),
        ];
    }
}
