<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;
class SubscriptionResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer->timezone;
        $nextPayment = null;
        if ($this->expired_at) {
            $nextPayment = $this->expired_at->setTimezone($timezone);
        }
        return [
            'id' => $this->id,
            'name' => $this->getName(),
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'next_payment' => $nextPayment ? $nextPayment->isoFormat(config('shaun_core.time_format.payment')) : null,
            'price' => $this->getPrice(),
            'gateway_name' => $this->getGateway()->name
        ];
    }
}
