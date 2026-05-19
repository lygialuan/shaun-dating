<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Bookmark\GetBookmarkValidate;
use Packages\ShaunSocial\Core\Http\Requests\Bookmark\StoreBookmarkValidate;
use Packages\ShaunSocial\Core\Repositories\Api\BookmarkRepository;

class BookmarkController extends ApiController
{
    protected $bookmarkRepository;

    public function __construct(BookmarkRepository $bookmarkRepository)
    {
        $this->bookmarkRepository = $bookmarkRepository;
        parent::__construct();        
    }

    public function store(StoreBookmarkValidate $request)
    {
        $this->bookmarkRepository->store($request->all(), $request->user()->id);
        return $this->successResponse();
    }

    public function get(GetBookmarkValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->bookmarkRepository->get($request->subject_type ,$page, $request->user());

        return $this->successResponse($result);
    }
}
