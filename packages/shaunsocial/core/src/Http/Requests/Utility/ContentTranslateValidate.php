<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Utility;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;

class ContentTranslateValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required',
            'subject_id' => 'required',
            'field' => 'required'
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

                if (! method_exists($subject, 'supportContentTranslate') || ! $subject->supportContentTranslate($data['field'])) {
                    return $validator->errors()->add('subject', __('The subject does not support content translate.'));
                }

                if (method_exists($subject, 'canView') && ! $subject->canView($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('You cannot view subject.'));
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
            'field.required' => __('The field id is required.'),
        ];
    }

    public function authorize()
    {
        return setting('content_translate.enable');
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}
