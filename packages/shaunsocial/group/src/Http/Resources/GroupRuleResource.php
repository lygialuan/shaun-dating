<?php


namespace Packages\ShaunSocial\Group\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Packages\ShaunSocial\Core\Traits\Utility;

class GroupRuleResource extends JsonResource
{
    use Utility;
    
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'number' => $this->getNumber(),
            'title' => $this->title,
            'description' => $this->makeContentHtml($this->description)
        ];
    }
}
