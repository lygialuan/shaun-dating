<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\UserPage\Models\UserPageCategory;

class StoreCategoryValidate extends PageValidate
{
    public function rules()
    {
        return [
            'categories' => [
                'required',
                function ($attribute, $categories, $fail) {
                    $check = true;
                    if (! is_array($categories)) {
                        return $fail(__('The category is not in the list.'));
                    }

                    foreach ($categories as $id) {
                        $category = UserPageCategory::findByField('id' ,$id);
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
            'categories.required' => __('The category is required.'),
        ];
    }
}
