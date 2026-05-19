<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;

class OpenidProviderResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'name' => $this->name,
            'photo' => $this->getPhoto().'?'.setting('site.cache_number'),
            'href' => $this->getHref(),
            'href_app' => $this->getHrefApp(),
        ];
    }
}
