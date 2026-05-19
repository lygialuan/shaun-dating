<?php

namespace Packages\ShaunSocial\AiFeatures\Repositories\Api;

use Packages\ShaunSocial\AiFeatures\Http\Resources\Compliance\ComplianceTaskResource;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;


class ComplianceRepository
{
    public function getTask($taskId)
    {
        $task = AiFeatureTask::findByField('id', $taskId);
        return new ComplianceTaskResource($task);
    }
}
