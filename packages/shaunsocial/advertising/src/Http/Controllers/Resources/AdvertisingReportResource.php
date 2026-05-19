<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class AdvertisingReportResource extends JsonResource
{
    public function toArray($request)
    {        
        $viewer = $request->user();
        return [
            'id' => $this->id,
            'date' => $this->date->setTimezone($viewer->timezone)->isoFormat(config('shaun_core.time_format.date')),
            'view_count' => formatNumberNoRound($this->view_count),
            'click_count' => formatNumberNoRound($this->click_count),
            'total_amount' => formatNumber($this->total_amount)
        ];
    }
}
