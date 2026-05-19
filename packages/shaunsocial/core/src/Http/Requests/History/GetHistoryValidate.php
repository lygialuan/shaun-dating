<?php


namespace Packages\ShaunSocial\Core\Http\Requests\History;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class GetHistoryValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num'
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

                if (! method_exists($subject, 'supportHistory')) {
                    return $validator->errors()->add('subject', __('The subject does not support history.'));
                }

                if (method_exists($subject, 'canView') && ! $subject->canView($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot view.'));
                }
            });
        }
    }

    public function messages()
    {
        return [
            'subject_type.required' => __('The subject is required.'),
            'subject_id.required' => __('The subject id is required.'),
        ];
    }
}
