<?php


namespace Packages\ShaunSocial\Core\Http\Requests\UserList;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserList;

class StoreListValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'nullable',
                function ($attribute, $id, $fail) {
                    if ($id) {
                        $viewer = $this->user();
                        $list = UserList::findByField('id', $id);
                        if (! $list || ! $list->isOwner($viewer->id)) {
                            return $fail(__('The user list is not found.'));
                        }
                    }
                },
            ],
            'name' => 'required|string|max:255',
        ];
    }

    public function messages()
    {
        return [
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 255 characters.')
        ];
    }
}
