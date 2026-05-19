<?php


namespace Packages\ShaunSocial\UserPage\Http\Requests;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;

class StoreReviewValidate extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'page_id' => [
                'required',
                function ($attribute, $pageId, $fail) {
                    $viewer = $this->user();
                    $isAdmin = $this->user()->isModerator();
                    $page = User::findByField('id', $pageId);

                    if ($pageId == $viewer->id) {
                        return $fail(__('You cannot review for yourself.'));
                    }

                    if (! $page || ! $page->isPage()) {
                        return $fail(__('The page is not found.'));
                    }

                    $pageInfo = $page->getPageInfo();
                    if (! $pageInfo->review_enable) {
                        return $fail(__('You cannot use this function.'));
                    }
    
                    if (! $page->canView($viewer->id) && !$isAdmin) {
                        return $fail(__('You cannot view this user.'));
                    } 

                    if ($viewer->getPageReview($pageId)) {
                        return $fail(__("You've already reviewed this page."));
                    }
                },
            ],
            'body' => ['nullable'],
            'is_recommend' => 'boolean'
        ];

        $rules['content'][]= 'max:'.getMaxTextSql(setting('feature.post_character_max'));

        return $rules;
    }

    public function messages()
    {
        return [
            'page_id.required' => __('The page id is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => getMaxTextSql(setting('feature.post_character_max'))])
        ];
    }
}
