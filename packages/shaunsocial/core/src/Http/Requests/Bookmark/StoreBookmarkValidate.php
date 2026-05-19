<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Bookmark;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreBookmarkValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num',
            'action' => 'required|string|in:add,remove',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewerId = $this->user()->id;
                $isAdmin = $this->user()->isModerator();

                $data = $validator->getData();
                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportBookmark')) {
                    return $validator->errors()->add('subject', __('The subject does not support bookmark.'));
                }

                if (method_exists($subject, 'canView') && ! $subject->canView($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot view.'));
                }

                if (method_exists($subject, 'canBookmark') && ! $subject->canBookmark($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot bookmark.'));
                }

                switch ($data['action']) {
                    case 'add':
                        if ($subject->getBookmark($viewerId)) {
                            return $validator->errors()->add('subject', __("You've already bookmarked this item."));
                        }
                        break;
                    case 'remove':
                        if (! $subject->getBookmark($viewerId)) {
                            return $validator->errors()->add('subject', __("You've already removed this item's bookmark."));
                        }
                        break;
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'subject_type.required' => __('The subject is required.'),
            'subject_id.required' => __('The subject id is required.'),
            'action.required' => __('The action is required.'),
            'action.in' => __('The action is not in the list (add,remove).'),
        ];
    }
}
