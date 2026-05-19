<?php

namespace Packages\ShaunSocial\AiFeatures\Http\Controllers\Api;

use Packages\ShaunSocial\AiFeatures\Http\Requests\Compliance\ValidateCompliance;
use Packages\ShaunSocial\AiFeatures\Repositories\Api\ComplianceRepository;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;

class ComplianceController extends ApiController
{
    public function __construct(
        protected ComplianceRepository $repository
    ) {
        parent::__construct();
    }

    public function view(ValidateCompliance $request)
    {
        $result = $this->repository->getTask($request->route('task'));

        return $this->successResponse($result);
    }
}
