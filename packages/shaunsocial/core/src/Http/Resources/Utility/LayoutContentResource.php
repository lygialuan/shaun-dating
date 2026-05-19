<?php


namespace Packages\ShaunSocial\Core\Http\Resources\Utility;

use Illuminate\Http\Resources\Json\JsonResource;

class LayoutContentResource extends JsonResource
{
    public function toArray($request)
    {
        $params = $this->getParams();
        $result = [
            'title' => $this->getTranslatedAttributeValue('title'),
            'content' => $this->getTranslatedAttributeValue('content'),
            'enable_title' => $this->enable_title ? true : false,
            'component' => $this->component,
            'package' => $this->package,
            'params' => $params,
            'type' => $this->type
        ];
        $data = [];
        if ($this->class) {
            $class = app($this->class);
            $data = $class->getData($request, $params);
        }
        $result['data'] = $data;
        
        return $result;
    }
}
