<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Hashtag;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class HashtagSearchValidate extends BaseFormRequest
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
