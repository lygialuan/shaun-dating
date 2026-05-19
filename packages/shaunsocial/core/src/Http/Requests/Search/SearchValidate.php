<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Search;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class SearchValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'required|string',
            'type' => 'required|string|in:post,user,page,group',
            'page' => 'integer'
        ];
    }
    
    public function messages()
    {
        return [
            'query.required' => __('The query is required.'),
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list (post,user,page,group).'),
            'page.integer' => __('The page must number.')
        ];
    }
}
