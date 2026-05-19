<?php


namespace Packages\ShaunSocial\UserSubscription\Http\Requests;

use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Requests\UserValidate;
use Packages\ShaunSocial\Core\Validation\PasswordVerifyValidation;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPlan;

class StoreUserSubscriptionTrialValidate extends UserValidate
{
    public function authorize()
    {
        $user = $this->user();
        return ! $user->isModerator();
    }

    public function rules()
    {
        return [
            'plan_id' =>  [
                'required',
                function ($attribute, $planId, $fail) {
                    $viewer = $this->user();
                    
                    $plan = UserSubscriptionPlan::findByField('id', $planId);
                    if (! $plan || ! $plan->canActive($viewer->id, true)) {
                        return $fail(__('The plan is not found.'));
                    }
                },
            ],
            'password' => [
                'required',
                new PasswordVerifyValidation(),
            ],
        ];
    }

    public function messages()
    {
        return [
            'plan_id.required' => __('The plan is required.'),
        ];
    }

    protected function failedAuthorization()
    {
        throw new MessageHttpException(__('You cannot use this function.'));
    }
}