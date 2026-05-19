<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\UserPage\Models\UserPageCategory;
use Packages\ShaunSocial\UserPage\Validation\PageAliasValidate;

class StorePageValidate extends UserValidate
{
    use Utility;
    
    public function authorize()
    {
        $authorize = parent::authorize();
        if ($authorize) {
            $user = $this->user();

            return $user->hasPermission('user_page.allow_create');
        }

        return $authorize;
    }

    public function rules()
    {
        return [
            'name' => 'required|string|max:64',
            'user_name' => [
                'required',
                'string',
                'max:128',
                new PageAliasValidate(),
                'unique:users,user_name'
            ],
            'description' => 'string|nullable|max:255',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();

                $this->checkPermissionActionLog('user_page.max_per_day', 'create_page', $user);
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 64 characters.'),
            'user_name.required' => __('The username is required.'),
            'user_name.unique' => __('The username has already been taken.'),
            'user_name.max' => __('The username must not be greater than 128 characters.'),
            'description.max' => __('The about must not be greater than 255 characters.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
