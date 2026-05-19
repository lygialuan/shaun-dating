<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Search;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class SuggestSearchValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'required|string',
        ];
    }
    
    public function messages()
    {
        return [
            'query.required' => __('The query is required.')
        ];
    }
}
