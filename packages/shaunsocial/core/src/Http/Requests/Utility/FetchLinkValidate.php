<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class FetchLinkValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'url' => 'required|url',
        ];
    }

    public function messages()
    {
        return [
            'url.required' => __('The url is required.'),
            'url.url' => __('The url is not validated.'),
        ];
    }
}
