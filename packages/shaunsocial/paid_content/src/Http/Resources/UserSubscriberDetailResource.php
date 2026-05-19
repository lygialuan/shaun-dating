<?php


namespace Packages\ShaunSocial\PaidContent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class UserSubscriberDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $subscription = $this->getSubscription();
        $viewer = $request->user();
        $timezone = $viewer->timezone;
        $nextPayment = null;
        if ($subscription->expired_at) {
            $nextPayment = $subscription->expired_at->setTimezone($timezone);
        }

        return [
            'id' => $this->id,
            'name' => $subscription->getName(),
            'user' => new UserResource($subscription->getUser(true)),
            'status' => $subscription->status,
            'status_text' => $subscription->getStatusText(),
            'next_payment' => $nextPayment ? $nextPayment->isoFormat(config('shaun_core.time_format.payment')) : null,
            'price' => $subscription->getPrice(),
            'gateway' => $subscription->getGateway()->name,
            'trial' => $subscription->trial_day ? __('Trial'). ': '.$subscription->trial_day .' ' .__('day(s)') : '',
            'created_at' => $subscription->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.payment')),
            'can_cancel' => $subscription->canCancel(),
            'can_resume' => $subscription->canResumeOnAdmin()
        ];
    }
}
