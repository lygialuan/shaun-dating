<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Language;

class StoreLanguageValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'key' => [
                'required',
                Rule::in(array_keys(Language::getAll(false)->pluck('name', 'key')->all()))
            ]
        ];
    }

    public function messages()
    {
        return [
            'key.required' => __('The language is required.'),
            'key.in' => __('The language not in the list.'),
        ];
    }
}
