<?php


namespace Packages\ShaunSocial\PaidContent\Http\Requests;

use Packages\ShaunSocial\PaidContent\Http\Requests\CreatorValidate;
use Packages\ShaunSocial\PaidContent\Models\UserSubscriber;

class StoreSubscriberResumeValidate extends CreatorValidate
{
    public function rules()
    {
        $viewer = $this->user();

        return [
            'id' => [
                'required',
                'alpha_num',
                function ($attribute, $id, $fail) use ($viewer) {
                    $subscriber = UserSubscriber::findByField('id', $id);
                    
                    if (! $subscriber || $subscriber->subscriber_id != $viewer->id || ! $subscriber->getSubscription()->canResumeOnAdmin()) {
                        return $fail(__('The subscriber is not found.')); 
                    }
                },
            ],
        ];
    }

    public function messages()
    {
        return [
            'id.required' => __('The id is required.'),
        ];
    }
}
