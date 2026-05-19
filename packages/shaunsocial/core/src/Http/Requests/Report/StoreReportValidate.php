<?php


namespace Packages\ShaunSocial\Core\Http\Requests\Report;

use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Models\ReportCategory;

class StoreReportValidate extends BaseFormRequest
{
    public function rules()
    {
        return [
            'subject_type' => 'required|string',
            'subject_id' => 'required|alpha_num',
            'category_id' => 'required',
            'reason' => 'string|nullable|max:255'
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $viewer = $this->user();
                $viewerId = $viewer->id;
                $isAdmin = $viewer->isModerator();
                $data = $validator->getData();
                $subject = findByTypeId($data['subject_type'], $data['subject_id']);
                if (! $subject) {
                    return $validator->errors()->add('subject', __('The subject is not found.'));
                }

                if (! method_exists($subject, 'supportReport')) {
                    return $validator->errors()->add('subject', __('The subject does not support report.'));
                }

                if (method_exists($subject, 'canReport') && ! $subject->canReport($viewerId) && !$isAdmin) {
                    return $validator->errors()->add('subject', __('The subject cannot report.'));
                }

                $categories = ReportCategory::getAll(false);                
                if (! in_array($data['category_id'], $categories->pluck('id')->all())) {
                    return $validator->errors()->add('subject', __('The category is not found.'));
                }

                if ($data['category_id'] == config('shaun_core.report.other_id')) {
                    if (empty($data['reason'])) {
                        return $validator->errors()->add('reason', __('The reason is required.'));
                    }
                }

                if ($subject->getReport($viewerId)) {
                    return $validator->errors()->add('subject', __("You've already reported this item."));
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
            'category_id.required' => __('The category is required.'),
            'reason.max' => __('The reason must not be greater than 255 characters.'),
        ];
    }
}
