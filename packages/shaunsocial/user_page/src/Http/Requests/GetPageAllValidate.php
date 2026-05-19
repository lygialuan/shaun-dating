<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\UserPage\Models\UserPageCategory;

class GetPageAllValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'keyword' => 'nullable',
            'category' => [
                'nullable',
                function ($attribute, $category, $fail) {
                    $category = UserPageCategory::findByField('id' ,$category);
                    if (! $category || $category->isDeleted())  {
                        return $fail(__('The category is required.'));
                    }
                },
            ],
            'page' => 'integer'
        ];
    }
    
    public function messages()
    {
        return [
            'page.integer' => __('The page must number.')
        ];
    }
}
