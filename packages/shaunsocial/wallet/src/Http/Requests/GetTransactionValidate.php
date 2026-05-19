<?php


namespace Packages\ShaunSocial\Wallet\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Wallet\Models\WalletTransactionType;

class GetTransactionValidate extends BaseFormRequest
{
    public function rules()
    {
        $rules = [
            'type' => ['required', Rule::in(array_keys(WalletTransactionType::getAllValue()))],
            'date_type' => ['required', Rule::in([
                'all', '30_day', '60_day', '90_day', 'custom'
            ])],
            'page' => 'integer',
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
            'type.required' => __('The type is required.'),
            'type.in' => __('The type is not in the list.'),
            'date_type.required' => __('The date type is required.'),
            'date_type.in' => __('The date type is not in the list.'),
            'page.integer' => __('The page must number.'),
            'from_date.date_format' => __('The from date format is invalid.'),
            'to_date.date_format' => __('The to date format is invalid.'),
        ];
    }
}
