<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Notification\AiFeatureRemovalNotification;
use Packages\ShaunSocial\Core\Services\Notification;

class AiFeatureAutoNotify
{
    public function __construct(
        protected Notification $notification
    ) {
    }

    public function handle(AiFeatureTask $task, bool $autoDeleted): bool
    {
        if (! $autoDeleted) {
            return false;
        }

        if (! (bool) data_get($task->result, 'flagged', false)) {
            return false;
        }

        $subject = $task->getSubject();
        if (! $subject || ! method_exists($subject, 'getUser')) {
            return false;
        }

        $user = $subject->getUser();
        if (! $user) {
            return false;
        }

        $this->notification->send($user, $user, AiFeatureRemovalNotification::class, $task, ['is_system' => true], 'shaun_ai_features');
        return true;
    }

    

}
