<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Http\Resources\Hashtag\HashtagResource;
use Packages\ShaunSocial\Core\Http\Resources\Post\PostResource;

class AdvertisingDetailResource extends JsonResource
{
    public function toArray($request)
    {
        $viewer = $request->user();
        $totalAmount = $this->getAmount(false);
        return [
            'id' => $this->id,
            'name' => $this->name,
            'post' => new PostResource($this->getPost()),
            'country_id' => $this->country_id,
            'state_id' => $this->state_id,
            'city_id' => $this->city_id,
            'zip_code' => $this->zip_code,
            'address' => $this->location,
            'gender' => $this->gender_id,
            'gender_text' => $this->gender_id ? $this->getGender()->getTranslatedAttributeValue('name') : __('All'),
            'age_from' => $this->age_from,
            'age_to' => $this->age_to,
            'age_type' => $this->age_type,
            'vat' => $this->vat,
            'hashtags' => HashtagResource::collection($this->getHashtags()),
            'address_full' => $this->getAddessFull(),
            'total_delivery_amount' => formatNumber($this->total_delivery_amount).' '. $this->currency,
            'total_available_amount' => formatNumber($totalAmount - $this->total_delivery_amount) . ' '. $this->currency,
            'total_amount' => formatNumber($totalAmount).' '. $this->currency,
            'daily_amount' => formatNumber($this->daily_amount).' '. $this->currency,
            'created_at' => $this->created_at->setTimezone($viewer->timezone)->diffForHumans(),
            'start' => $this->start->setTimezone($viewer->timezone)->isoFormat(config('shaun_core.time_format.date')),
            'end' => $this->end->setTimezone($viewer->timezone)->isoFormat(config('shaun_core.time_format.date')),
            'status' => $this->status,
            'canEdit' => $this->canEdit($viewer->id),
            'canStop' => $this->canStop($viewer->id),
            'canEnable' => $this->canEnable($viewer->id)
        ];
    }
}
