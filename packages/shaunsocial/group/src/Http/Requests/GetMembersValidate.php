<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;

class GetMembersValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    $group = Group::findByField('id', $id);
                    if (! $group) {
                        return $fail(__('The group is not found.'));
                    }

                    if (! $group->canView($viewerId) && ! $isAdmin) {
                        return $fail(__('The group is not found.'));
                    }
                }
            ],
            'query' => 'nullable|string',
            'page' => 'integer'
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'page.integer' => __('The page must number.')
        ];
    }
}
