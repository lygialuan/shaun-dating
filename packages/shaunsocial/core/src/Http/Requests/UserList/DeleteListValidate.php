<?php


namespace Packages\ShaunSocial\Core\Http\Requests\UserList;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\UserList;

class DeleteListValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $list = UserList::findByField('id', $id);
                    if (! $list || ! $list->isOwner($viewer->id)) {
                        return $fail(__('The user list is not found.'));
                    }
                }
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
