<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class GetReviewValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'page_id' => [
                'required',
                function ($attribute, $pageId, $fail) {
                    $viewer = $this->user();
                    $viewerId = $viewer ? $viewer->id : 0;
                    $isAdmin = $viewer ? $viewer->isModerator() : false;
                    $page = User::findByField('id', $pageId);
                    
                    if (! $page || ! $page->isPage()) {
                        return $fail(__('The page is not found.'));
                    }

                    $pageInfo = $page->getPageInfo();
                    if (! $pageInfo->review_enable && $viewerId != $pageId) {
                        return $fail(__('You cannot use this function.'));
                    }
    
                    if (! $page->canView($viewerId) && !$isAdmin) {
                        return $fail(__('You cannot view this user.'));
                    }
                },
            ]
        ];
    }

    public function messages()
    {
        return [
            'page_id.required' => __('The page id is required.'),
        ];
    }
}
