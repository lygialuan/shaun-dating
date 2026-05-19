<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\History\GetHistoryValidate;
use Packages\ShaunSocial\Core\Repositories\Api\HistoryRepository;

class HistoryController extends ApiController
{
    protected $historyRepository;

    public function __construct(HistoryRepository $historyRepository)
    {
        $this->historyRepository = $historyRepository;
        parent::__construct();
    }

    public function get(GetHistoryValidate $request)
    {
        $page = $request->page ? $request->page : 1;

        $result = $this->historyRepository->get($request->subject_type, $request->subject_id, $page);

        return $this->successResponse($result);
    }
}
