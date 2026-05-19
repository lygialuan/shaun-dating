<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Search;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreSearchHistoryValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => [
                'required',
                'max:255'
            ]
        ];
    }

    public function messages()
    {
        return [
            'query.required' => __('The query is required'),
            'query.max' => __('The name must not be greater than 255 characters.'),
        ];
    }
}
