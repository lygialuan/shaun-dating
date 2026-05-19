<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Report\StoreReportValidate;
use Packages\ShaunSocial\Core\Repositories\Api\ReportRepository;
use Illuminate\Http\Request;

class ReportController extends ApiController
{
    protected $reportRepository;

    public function __construct(ReportRepository $reportRepository)
    {
        $this->reportRepository = $reportRepository;
        parent::__construct();
    }

    public function category(Request $request)
    {
        $result = $this->reportRepository->category();

        return $this->successResponse($result);
    }

    public function store(StoreReportValidate $request)
    {
        $request->mergeIfMissing([
            'reason' => ''
        ]);
        
        $this->reportRepository->store($request->all(), $request->user()->id);

        return $this->successResponse();
    }
}
