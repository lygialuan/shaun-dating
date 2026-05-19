<?php

namespace Packages\ShaunSocial\Core\Http\Resources\SearchHistory;

use Illuminate\Http\Resources\Json\JsonResource;

class SearchHistoryResource extends JsonResource
{
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'query' => $this->query,
            'created_at' => $this->created_at
        ];
    }
} 