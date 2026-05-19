<?php


namespace Packages\ShaunSocial\Core\Http\Requests\User;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class SearchUserValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'string|nullable',
            'page' => 'integer'
        ];
    }
    
    public function messages()
    {
        return [
            'query.string' => __('The query must be string.'),
            'page.integer' => __('The page must number.')
        ];
    }
}
