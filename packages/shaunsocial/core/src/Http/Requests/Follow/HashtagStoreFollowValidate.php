<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Follow;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\Hashtag;

class HashtagStoreFollowValidate extends BaseFormRequest
{
    public function rules()
    {
        $action = $this->input('action');
        return [
            'name' => [
                'required',
                'string',
                function ($attribute, $hashtag, $fail) use ($action) {
                    if (! checkHashtag($hashtag)) {
                        return $fail(__('The hashtag is not validated.'));
                    }

                    $item = Hashtag::findByField('name', $hashtag);
                    if (!$item) {
                        return $fail(__('The hashtag is not found.'));
                    }

                    if ($action != 'unfollow') {
                        if (!$item->is_active) {
                            return $fail(__('The hashtag is not found.'));
                        }                        
                    }
                },
            ],
            'action' => 'required|string|in:follow,unfollow',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $viewer = $this->user();

                switch ($data['action']) {
                    case 'follow':
                        if ($viewer->getHashtagFollow($data['name'])) {
                            return $validator->errors()->add('hashtag', __("You've already followed this hashtag."));
                        }
                        break;
                    case 'unfollow':
                        if (! $viewer->getHashtagFollow($data['name'])) {
                            return $validator->errors()->add('hashtag', __("You've already unfollowed this hashtag."));
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
            'id.required' => __('The user id is required.'),
            'action.required' => __('The action is required.'),
            'action.in' => __('The action is not in the list (add,remove).'),
        ];
    }
}
