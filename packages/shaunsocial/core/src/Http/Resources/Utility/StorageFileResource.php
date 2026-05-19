<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;

class StorageFileResource extends JsonResource
{
    public function toArray($request)
    {
        $result = [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'url' => $this->getUrl(),
            'params' => $this->getParams(),
            'name' => $this->name
        ];

        if ($this->has_child) {
            $childs = $this->getChilds();
            $result['childs'] = $childs->map(function ($item, $key) {
                return [
                    $item->type => [
                        'url' => $item->getUrl(),
                        'params' => $item->getParams(),
                    ],
                ];
            })->toArray();
        }

        return $result;
    }
}
