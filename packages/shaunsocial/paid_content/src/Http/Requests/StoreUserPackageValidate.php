<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Illuminate\Validation\Rule;
use Packages\ShaunSocial\PaidContent\Enum\SubscriberPackageType;
use Packages\ShaunSocial\PaidContent\Http\Requests\CreatorValidate;
use Packages\ShaunSocial\PaidContent\Models\SubscriberPackage;

class StoreUserPackageValidate extends CreatorValidate
{
    public function rules()
    {
        return [
            'monthly_id' => [
                function ($attribute, $monthlyId, $fail) {
                    if ($monthlyId) {
                        $package = SubscriberPackage::findByField('id', $monthlyId);
                    
                        if (! $package || ! $package->canSubscriber() || $package->type != SubscriberPackageType::MONTHLY) {
                            return $fail(__('The package monthly is not found.')); 
                        }
                    }
                },
            ],
            'quarterly_id' => [
                function ($attribute, $quarterlyId, $fail) {
                    if ($quarterlyId) {
                        $package = SubscriberPackage::findByField('id', $quarterlyId);
                    
                        if (! $package || ! $package->canSubscriber() || $package->type != SubscriberPackageType::QUARTERLY) {
                            return $fail(__('The package quarterly is not found.')); 
                        }
                    }
                },
            ],
            'biannual_id' => [
                function ($attribute, $biannualId, $fail) {
                    if ($biannualId) {
                        $package = SubscriberPackage::findByField('id', $biannualId);
                    
                        if (! $package || ! $package->canSubscriber() || $package->type != SubscriberPackageType::BIANNUAL) {
                            return $fail(__('The package biannual is not found.')); 
                        }
                    }
                },
            ],
            'annual_id' => [
                function ($attribute, $annualId, $fail) {
                    if ($annualId) {
                        $package = SubscriberPackage::findByField('id', $annualId);
                    
                        if (! $package || ! $package->canSubscriber() || $package->type != SubscriberPackageType::ANNUAL) {
                            return $fail(__('The package annual is not found.')); 
                        }
                    }
                },
            ],
            'default' => [
                'nullable',
                Rule::in(SubscriberPackageType::values())
            ],
            'paid_content_trial_day' => [
                'nullable',
                'integer',
                'max:730'
            ]
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $user = $this->user();
                
                $data = $validator->getData();
                if (!empty($data['default'])) {
                    $check = true;

                    switch ($data['default']) {
                        case SubscriberPackageType::MONTHLY:
                            if (empty($data['monthly_id'])) {
                                $check = false;
                            }
                            break;
                        case SubscriberPackageType::QUARTERLY:
                            if (empty($data['quarterly_id'])) {
                                $check = false;
                            }
                            break;
                        case SubscriberPackageType::BIANNUAL:
                            if (empty($data['biannual_id'])) {
                                $check = false;
                            }
                            break;
                        case SubscriberPackageType::ANNUAL:
                            if (empty($data['annual_id'])) {
                                $check = false;
                            }
                            break;
                    }

                    if (! $check)  {
                        return $validator->errors()->add('default', __('The default package does not exist.'));
                    }
                }
            });
        }

        return $validator;
    }

    public function messages()
    {
        return [
            'paid_content_trial_day.integer' => __('The trial day must be integer.'),
            'paid_content_trial_day.max' => __('The trial days must not be greater than 730 days')
        ];
    }
}
