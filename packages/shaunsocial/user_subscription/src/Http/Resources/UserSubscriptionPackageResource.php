<?php


namespace Packages\ShaunSocial\UserSubscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class UserSubscriptionPackageResource extends JsonResource
{
    public function toArray($request)
    {
        $plans = $this->getPlans();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->getTranslatedAttributeValue('description'),
            'is_highlight' => $this->is_highlight,
            'plans' =>  UserSubscriptionPackagePlanResource::collection($plans)
        ];
    }
}
