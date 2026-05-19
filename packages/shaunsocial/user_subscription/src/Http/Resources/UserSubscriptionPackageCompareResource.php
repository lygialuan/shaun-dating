<?php


namespace Packages\ShaunSocial\UserSubscription\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPackageCompareColumn;

class UserSubscriptionPackageCompareResource extends JsonResource
{
    private static $packages;

    public function toArray($request)
    {
        $data = [
            'name' => $this->getTranslatedAttributeValue('name')
        ];
        self::$packages->each(function($package) use (&$data){
            $column = UserSubscriptionPackageCompareColumn::getColumnByCompareIdPackageId($this->id, $package->id);
            $data['packages'][] = new UserSubscriptionPackageCompareColumnResource($column);
        });
        
        return $data;
    }

    public static function customCollection($resource, $packages)
    {
        self::$packages = $packages;
        return parent::collection($resource);
    }
}
