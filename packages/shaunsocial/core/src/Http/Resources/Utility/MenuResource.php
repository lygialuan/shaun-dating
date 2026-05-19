<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class MenuResource extends JsonResource
{
    public function toArray($request)
    {
        $language = App::getLocale();
        $result = [
            'name' => $this->getTranslatedAttributeValue('name', $language),
            'url' => ($this->type == 'internal') ? '/'.$this->url : $this->url,
            'is_new_tab' => $this->is_new_tab ? true : false,
            'is_header' => $this->is_header ? true : false,
            'type' => $this->type,
            'is_core' => $this->is_core,
            'alias' => $this->alias,
            'icon' => $this->getIcon(),
            'childs' => MenuResource::collection($this->childs)
        ];

        return $result;
    }
}
