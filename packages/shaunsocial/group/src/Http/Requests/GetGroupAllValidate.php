<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupCategory;

class GetGroupAllValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'keyword' => 'nullable',
            'category' => [
                'nullable',
                function ($attribute, $category, $fail) {
                    $category = GroupCategory::findByField('id' ,$category);
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
