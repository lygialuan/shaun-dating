<?php


namespace Packages\ShaunSocial\UserSubscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionPackagePlanResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();

        return [
            'id' => $this->id,
            'trial_day' => $viewer->user_subscription_has_trial ? 0 : $this->trial_day,
            'google_price_id' => $this->google_price_id,
            'apple_price_id' => $this->apple_price_id,
            'description' => $this->getDescription(),
            'name' => $this->getTranslatedAttributeValue('name'),
            'gateways' => $this->gateways
                ->map(function ($gateway) {
                    return [
                        'id' => $gateway->id,
                        'type' => 'gateway',
                        'key' => $gateway->key,
                        'name' => $gateway->name,
                        'flex_form_id' => $gateway->pivot->flex_form_id,
                    ];
                })
                ->when(setting('shaun_wallet.enable'), function ($collection) {
                    return $collection->push([
                        'id' => 'wallet',
                        'type' => 'wallet',
                        'key' => 'wallet',
                        'name' => __('Use credit balance (:wallet available)'),
                    ]);
                })
                ->values(),
        ];
    }
}
