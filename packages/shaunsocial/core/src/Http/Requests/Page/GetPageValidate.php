<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Page;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Page;

class GetPageValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'slug' => [
                'required',
                function ($attribute, $slug, $fail) use ($viewer) {
                    $page = Page::findByField('slug', $slug);
                    $isAdmin = $viewer ? $viewer->isModerator() : false;

                    if (! $page) {
                        return $fail(__('The page is not found.'));
                    }
                    $roleId = $viewer ? $viewer->role_id : config('shaun_core.role.id.guest');
                    if (! $page->hasPermission($roleId) && !$isAdmin) {
                        return $fail(__('You cannot view this page.'));
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'slug.required' => __('The slug is required.'),
        ];
    }
}
