<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Like;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetLikeValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num',
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();
                $viewerId = $viewer ? $viewer->id : 0;
                $isAdmin = $viewer ? $viewer->isModerator() : false;
                $data = $validator->getData();
                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportLike')) {
                    return $validator->errors()->add('subject', __('The subject does not support like.'));
                }

                if (method_exists($subject, 'canView') && ! $subject->canView($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('You cannot view it.'));
                }

                if (method_exists($subject, 'canViewLike') && ! $subject->canViewLike($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('You cannot view like.'));
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
        ];
    }
}
