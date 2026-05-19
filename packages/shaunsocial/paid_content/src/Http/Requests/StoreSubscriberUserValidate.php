<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\BaseFormRequest;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\PaidContent\Models\SubscriberTrial;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriberPackage;

class StoreSubscriberUserValidate extends BaseFormRequest
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $id, $fail) use ($viewer) {
                    $userPackage = UserSubscriberPackage::findByField('id', $id);
                    $check = false;
                    if ($userPackage && $userPackage->getPackage()->canSubscriber()) {
                        $user = $userPackage->getUser();
                        if ($user && $user->canSubscriber($viewer->id)) {
                            $check = true;
                        }
                    }
                    
                    if (! $check) {
                        return $fail(__('The package is not found.')); 
                    }
                },
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
            'ref_code' => [
                'nullable'
            ]
        ];
    }

    public function withValidator($validator)
    {
        if (! $validator->fails()) {
            $validator->after(function ($validator) {
                $data = $validator->getData();
                $viewer =  $this->user();
                $userPackage = UserSubscriberPackage::findByField('id', $data['id']);
                $amount = $userPackage->getPackage()->amount;
                $user = $userPackage->getUser();
                if ($user->paid_content_trial_day && ! SubscriberTrial::getSubscriberTrial($viewer->id, $user->id)) {
                    $amount = 0;
                }

                if ($amount && $viewer->getCurrentBalance() < $amount) {
                    throw new MessageHttpException(__("You don't have enough balance"));
                }
            });
        }

        return $validator;
    }
    
    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
            'password.required' => __('The password is required.'),
        ];
    }
}
