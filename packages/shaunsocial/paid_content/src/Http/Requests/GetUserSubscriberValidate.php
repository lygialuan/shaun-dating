<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Enum\SubscriptionStatus;
use Packages\ShaunSocial\PaidContent\Http\Requests\CreatorValidate;

class GetUserSubscriberValidate extends CreatorValidate
{
    public function rules()
    {
        $rules = [
            'status' => [
                'required',
                Rule::in(['all']+ SubscriptionStatus::values())
            ],
            'date_type' => ['required', Rule::in([
                'all', '30_day', '60_day', '90_day', 'custom'
            ])],
            'page' => 'integer',
            'keyword' => 'nullable',
            'from_date' => 'nullable|date_format:Y-m-d',
            'to_date' => 'nullable|date_format:Y-m-d',
        ];

        return $rules;
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                
                $data = $validator->getData();
                if ($data['date_type'] == 'custom') {
                    if (! empty($data['from_date']) && ! empty($data['to_date']) && strtotime($data['to_date']) < strtotime($data['from_date'])) {
                        return $validator->errors()->add('to_date', __('To Date should be greater than From Date.'));
                    }
                }
            });
        }

        return $validator;
    }
    
    public function messages()
    {
        return [
            'status.required' => __('The status is required.'),
            'status.in' => __('The status is not in the list.'),
            'date_type.required' => __('The date type is required.'),
            'date_type.in' => __('The date type is not in the list.'),
            'page.integer' => __('The page must number.'),
            'from_date.date_format' => __('The from date format is invalid.'),
            'to_date.date_format' => __('The to date format is invalid.'),
        ];
    }
}
