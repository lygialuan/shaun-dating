<?php


namespace Packages\ShaunSocial\UserSubscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\UserSubscription\Enum\UserSubscriptionPackageCompareColumnType;

class UserSubscriptionPackageCompareColumnResource extends JsonResource
{
    public function toArray($request)
    {
        $value = '';
        switch ($this->type) {
            case UserSubscriptionPackageCompareColumnType::TEXT:
                $value = $this->getTranslatedAttributeValue('value');
                break;
            case UserSubscriptionPackageCompareColumnType::BOOLEAN:
                $value = $this->value;
                break;
        }
        return [
            'value' => $value,
            'type' => $this->type
        ];
    }
}
