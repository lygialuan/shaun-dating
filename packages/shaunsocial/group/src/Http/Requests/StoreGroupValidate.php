<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Enum\GroupType;
use Packages\ShaunSocial\Group\Models\GroupCategory;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Traits\Utility;

class StoreGroupValidate extends BaseFormRequest
{
    use Utility;
    
    public function authorize()
    {
        $user = $this->user();
        
        return $user->hasPermission('group.allow_create');
    }

    public function rules()
    {
        $types = GroupType::values();
        return [
            'name' => 'required|string|max:255',
            'type' => ['required', Rule::in($types)],
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
            'description' => 'string|nullable|max:1024',
            'hashtags' => [
                'nullable',
                function ($attribute, $hashtags, $fail) {
                    $check = true;
                    if (! is_array($hashtags)) {
                        return $fail(__('The hashtag is not in the list.'));
                    }
                    if (count($hashtags)) {
                        foreach ($hashtags as $hashtag) {
                            if ($hashtag) {
                                if (! checkHashtag($hashtag))  {
                                    $check = false;
                                }
                            }
                        }
    
                        if (! $check) {
                            return $fail(__('The hashtag is required.'));
                        }
                    }
                },
            ],
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();

                $this->checkPermissionActionLog('group.max_per_day', 'create_group', $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 255 characters.'),
            'categories.required' => __('The category is required.'),
            'description.max' => __('The description must not be greater than 1024 characters.'),
            'type.required' =>  __('The type is required.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
