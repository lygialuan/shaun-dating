<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Bookmark;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\ModelMap;

class GetBookmarkValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required|string'
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {

                $data = $validator->getData();
                $subjectModel = ModelMap::getModel($data['subject_type']);
                if (! $subjectModel) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                $subject = new $subjectModel();
                if (! method_exists($subject, 'supportBookmark')) {
                    return $validator->errors()->add('subject', __('The subject does not support bookmark.'));
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'subject_type.required' => __('The subject is required.'),
        ];
    }
}
