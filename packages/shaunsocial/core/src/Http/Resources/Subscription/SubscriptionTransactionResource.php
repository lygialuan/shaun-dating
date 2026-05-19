<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;
class SubscriptionTransactionResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $timezone = $viewer->timezone;
        return [
            'id' => $this->id,
            'date' => $this->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.payment')),
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'price' => $this->getPrice(),
            'transaction_id' => $this->getTransactionId(),
            'currency' => $this->currency
        ];
    }
}
