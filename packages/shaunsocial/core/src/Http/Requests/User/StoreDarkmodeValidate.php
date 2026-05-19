<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreDarkmodeValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'darkmode' => [
                'required',
                Rule::in([
                    'auto',
                    'on',
                    'off'
                ])
            ]
        ];
    }

    public function messages()
    {
        return [
            'enable.required' => __('The enable is required.'),
        ];
    }
}
