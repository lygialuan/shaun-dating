<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Comment;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetCommentValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required|alpha_num',
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

                if (! method_exists($subject, 'supportComment')) {
                    return $validator->errors()->add('subject', __('The subject does not support comment.'));
                }

                if (method_exists($subject, 'canViewComment') && ! $subject->canViewComment($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('You cannot view comment.'));
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
