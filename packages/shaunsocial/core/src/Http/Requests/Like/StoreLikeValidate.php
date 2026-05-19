<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Like;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class StoreLikeValidate extends BaseFormRequest
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
                $viewer = $this->user();
                $viewerId = $this->user()->id;
                $isAdmin = $this->user()->isModerator();
                $data = $validator->getData();
                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportLike')) {
                    return $validator->errors()->add('subject', __('The subject does not support like.'));
                }

                if (method_exists($subject, 'canLike') && ! $subject->canLike($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot like.'));
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
