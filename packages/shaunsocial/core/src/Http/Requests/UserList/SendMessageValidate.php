<?php


namespace Packages\ShaunSocial\Core\Http\Requests\UserList;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Models\UserList;

class SendMessageValidate extends BaseFormRequest
{
    public function rules()
    {
        $types = [
            'follower',
            'following',
            'subscriber',
            'list',
            'new_list',
            'specific'
        ];

        if (! setting('shaun_paid_content.enable')) {
            unset($types['subscriber']);
        }

        $rules = [
            'type' => ['required', Rule::in($types)],
            'subject_type' => 'required',
            'subject_id' => 'required',
            'content' => ['required']
        ];

        $viewer = $this->user();

        if ($this->input('type') == 'list') {
            $rules['list_id'] = [
                'required',
                function ($attribute, $listId, $fail)  use ($viewer){
                    $list = UserList::findByField('id', $listId);
                    if (! $list || ! $list->canSend($viewer->id)) {
                        return $fail(__('The user list is not found.'));
                    }
                }
            ];
        }

        if ($this->input('type') == 'new_list' || $this->input('type') == 'specific') {
            if ($this->input('type') == 'new_list') {
                $rules['name'] = 'required|string|max:255';
            }
            $rules['user_ids'] = [
                'required',
                function ($attribute, $userIds, $fail) {
                    if (! is_array($userIds)) {
                        return $fail(__('The user ids is required.'));
                    }

                    if (! count($userIds)) {
                        return $fail(__('The user ids is required.'));
                    }

                    if (count($userIds) > config('shaun_core.core.limit_save_auto')) {
                        return $fail(__('Number of user that can be add per time is :1.', ['1' => config('shaun_core.core.limit_save_auto')]));
                    }

                    $viewer = $this->user();

                    foreach ($userIds as $id) {
                        if ($id == $viewer->id) {
                            return $fail(__('You cannot add themselves.'));
                        }
                        $user = User::findByField('id', $id);
                        if (! $user) {
                            return $fail(__('The user is not found.'));
                        }
                    }
                }
            ];
        }

        if (setting('chat.send_text_max')) {
            $rules['content'][] = 'max:'.setting('chat.send_text_max');
        }

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();
                $viewerId = $viewer->id;
                $isAdmin = $viewer->isModerator();
                $data = $validator->getData();
                if ($data['type'] == 'follower') {
                    if (! $viewer->follower_count) {
                        return $validator->errors()->add('type', __('You do not have follower.'));
                    }
                }

                if ($data['type'] == 'following') {
                    if (! $viewer->following_count) {
                        return $validator->errors()->add('type', __('You do not have following.'));
                    }
                }

                if ($data['type'] == 'subscriber') {
                    if (! $viewer->subscriber_count) {
                        return $validator->errors()->add('type', __('You do not have subscriber.'));
                    }
                }

                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (method_exists($subject, 'canView') && ! $subject->canView($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot view.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'list_id.required' => __('The list id is required.'),
            'type.required' => __('The type is required.'),
            'type.in' => __('The type not in list.'),
            'user_ids.required' => __('The user ids is required.'),
            'name.required' => __('The name is required.'),
            'name.max' => __('The name must not be greater than 255 characters.'),
            'subject_type.required' => __('The subject type is required.'),
            'subject_id.required' => __('The subject id is required.'),
            'content.required' => __('The content is required.'),
            'content.max' => __('You have reached your maximum limit of characters allowed. Please limit your content to :number characters or less.', ['number' => setting('chat.send_text_max')])
        ];
    }
}
