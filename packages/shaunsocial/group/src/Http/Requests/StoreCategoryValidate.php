<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\GroupCategory;
use Packages\ShaunSocial\Group\Validation\GroupAdminValidate;

class StoreCategoryValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                new GroupAdminValidate(),
            ],
            'categories' => [
                'required',
                function ($attribute, $categories, $fail) {
                    $check = true;
                    if (! is_array($categories)) {
                        return $fail(__('The category is not in the list.'));
                    }

                    foreach ($categories as $id) {
                        $category = GroupCategory::findByField('id' ,$id);
                        if (! $category || $category->isDeleted())  {
                            $check = false;
                        }
                    }

                    if (! $check) {
                        return $fail(__('The category is required.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'categories.required' => __('The category is required.'),
        ];
    }
}
