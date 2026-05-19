<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Group\Models\Group;

class GetPostWithHashtagValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $group = Group::findByField('id', $id);
                    if (! $group) {
                        return $fail(__('The group is not found.'));
                    }
                    
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $group->canView($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view post this group.'));
                    }
                }
            ],
            'name' => [
                'required',
                function ($attribute, $name, $fail) {
                    $hashtag = Hashtag::findByField('name', $name);
                    if (! $hashtag) {
                        return $fail(__('The hashtag is not found.'));
                    }
                }
            ]
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'name.required' =>  __('The name is required.'),
        ];
    }
}
