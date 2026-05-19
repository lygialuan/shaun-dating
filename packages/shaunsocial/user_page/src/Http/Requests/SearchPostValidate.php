<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class SearchPostValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'query' => 'string|required',
            'id' => [
                'required',
                function ($attribute, $id, $fail) {
                    $page = User::findByField('id', $id);
                    if (! $page || ! $page->isPage()) {
                        return $fail(__('The post is not found.'));
                    }
                    
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $page->canView($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view this user.'));
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
