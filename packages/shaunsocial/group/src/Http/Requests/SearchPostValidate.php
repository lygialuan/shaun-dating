<?php


namespace Packages\ShaunSocial\Group\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Group\Models\Group;

class SearchPostValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'string|required',
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
            'page' => 'integer'
        ];
    }
    
    public function messages()
    {
        return [
            'query.required' => __('The query is required.'),
            'query.string' => __('The query must be string.'),
            'page.integer' => __('The page must number.'),
            'id.required' => __('The id is required.')
        ];
    }
}
