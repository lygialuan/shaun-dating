<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Models\Bookmark;
use Packages\ShaunSocial\Core\Traits\ApiResponser;
use Packages\ShaunSocial\Core\Traits\HasUserList;

class BookmarkRepository
{
    use ApiResponser, HasUserList;

    public function store($data, $viewerId)
    {
        $subject = findByTypeId($data['subject_type'], $data['subject_id']);

        switch ($data['action']) {
            case 'add':
                $subject->addBookmark($viewerId);
                break;
            case 'remove':
                $bookmark = $subject->getBookmark($viewerId);
                $bookmark->delete();
                break;
        }
    }

    public function get($subjectType ,$page, $viewer)
    {
        $bookmarks = Bookmark::getCachePagination('bookmark_'.$viewer->id, Bookmark::where('user_id', $viewer->id)->where('subject_type', $subjectType)->orderBy('id', 'DESC'), $page);
        $bookmarksNextPage = Bookmark::getCachePagination('bookmark_'.$viewer->id, Bookmark::where('user_id', $viewer->id)->where('subject_type', $subjectType)->orderBy('id', 'DESC'), $page + 1);
        $bookmarks = $this->filterSubjectList($bookmarks,$viewer,'user_id');

        $results = [];
        foreach ($bookmarks as $bookmark) {
            $results[] = $bookmark->getSubjectResource();
        }

        return [
            'items' => $results,
            'has_next_page' => count($bookmarksNextPage) ? true : false
        ];
    }
}
