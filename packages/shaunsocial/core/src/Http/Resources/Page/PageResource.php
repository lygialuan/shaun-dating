<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Page;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\App;

class PageResource extends JsonResource
{
    public function toArray($request)
    {
        $language = App::getLocale();
        
        return [
            'id' => $this->id,
            'title' => $this->getTranslatedAttributeValue('title',$language),
            'content' => $this->getTranslatedAttributeValue('content',$language),
        ];
    }
}
