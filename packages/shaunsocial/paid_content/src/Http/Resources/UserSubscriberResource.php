<?php


namespace Packages\ShaunSocial\PaidContent\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Subscription\SubscriptionResource;
use Packages\ShaunSocial\Core\Http\Resources\User\UserResource;

class UserSubscriberResource extends JsonResource
{
    public function toArray($request)
    {
        $subscription = $this->getSubscription();
        $viewer = $request->user();
        $timezone = $viewer->timezone;
        return [
            'id' => $this->id,
            'user' => new UserResource($subscription->getUser(true)),
            'name' => $subscription->getName(),
            'status' => $subscription->status,
            'status_text' => $subscription->getStatusText(),
            'price' => $subscription->getPrice(),
            'created_at' => $subscription->created_at->setTimezone($timezone)->toDateTimeString(),
            'canCancel' => $subscription->canCancel(),
            'canResume' => $subscription->canResumeOnAdmin()
        ];
    }
}
