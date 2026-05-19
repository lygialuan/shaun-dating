<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\History\HistoryResource;
use Packages\ShaunSocial\Core\Models\History;

class HistoryRepository
{
    public function get($subjectType , $subjectId, $page)
    {
        $histories = History::getCachePagination('history_'.$subjectType.'_'.$subjectId, History::where('subject_type', $subjectType)->where('subject_id', $subjectId)->orderBy('id', 'ASC'), $page);
        $historiesNextPage = History::getCachePagination('history_'.$subjectType.'_'.$subjectId, History::where('subject_type', $subjectType)->where('subject_id', $subjectId)->orderBy('id', 'ASC'), $page + 1);

        return [
            'items' => HistoryResource::collection($histories),
            'has_next_page' => count($historiesNextPage) ? true : false
        ];
    }
}
