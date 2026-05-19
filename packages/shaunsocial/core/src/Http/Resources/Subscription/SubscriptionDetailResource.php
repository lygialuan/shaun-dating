<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Subscription;

use Illuminate\Http\Resources\Json\JsonResource;
class SubscriptionDetailResource extends JsonResource
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
            'subject' => $this->getSubjectResource(),
            'type' => $this->type,
            'status' => $this->status,
            'status_text' => $this->getStatusText(),
            'next_payment' => $nextPayment ? $nextPayment->isoFormat(config('shaun_core.time_format.payment')) : null,
            'price' => $this->getPrice(),
            'gateway' => $this->getGateway()->name,
            'can_cancel' => $this->canCancel(),
            'can_resume' => $this->canResume(),
            'trial' => $this->trial_day ? __('Trial'). ': '.$this->trial_day .' ' .__('day(s)') : '',
            'created_at' => $this->created_at->setTimezone($timezone)->isoFormat(config('shaun_core.time_format.payment'))
        ];
    }
}
