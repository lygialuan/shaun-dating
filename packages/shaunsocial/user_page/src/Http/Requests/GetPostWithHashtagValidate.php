<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Hashtag;
use Packages\ShaunSocial\Core\Models\User;

class GetPostWithHashtagValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
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
