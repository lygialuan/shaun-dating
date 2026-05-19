<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisingResource extends JsonResource
{
    public function toArray($request)
    {        
        $viewer = $request->user();
        return [
            'id' => $this->id,
            'name' => $this->name,
            'status' => $this->status,
            'created_at' => $this->created_at->setTimezone($viewer->timezone)->diffForHumans(),
            'view_count' => formatNumberNoRound($this->view_count),
            'click_count' => formatNumberNoRound($this->click_count),
            'total_delivery_amount' => formatNumber($this->total_delivery_amount),
            'canEdit' => $this->canEdit($viewer->id),
            'canStop' => $this->canStop($viewer->id),
            'canEnable' => $this->canEnable($viewer->id)
        ];
    }
}
