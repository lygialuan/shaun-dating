<?php

namespace Packages\ShaunSocial\AiFeatures\Services;

use Illuminate\Support\Collection;
use Packages\ShaunSocial\AiFeatures\Models\AiFeatureTask;
use Packages\ShaunSocial\AiFeatures\Notification\AiFeatureAdminReportNotification;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Services\Notification;

class AiFeatureAutoNotifyForAdmin
{
    public function __construct(
        protected Notification $notification
    ) {
    }

    public function handle(AiFeatureTask $task): bool
    {
        if (! config('shaun_ai_features.reporting.C', true)) {
            return false;
        }

        if (! (bool) data_get($task->result, 'flagged', false)) {
            return false;
        }

        $admins = $this->getAdminRecipients();
        if ($admins->isEmpty()) {
            return false;
        }

        $from = $from = $admins->first();

        $this->notification->send(
            $admins,
            $from,
            AiFeatureAdminReportNotification::class,
            $task,
            ['is_system' => true],
            'shaun_ai_features',
            true,
            false
        );

        return true;
    }

    protected function getAdminRecipients(): Collection
    {
        $roleIds = Role::query()
            ->where('is_supper_admin', true)
            ->orWhere('is_moderator', true)
            ->pluck('id');

        if ($roleIds->isEmpty()) {
            return collect();
        }

        return User::whereIn('role_id', $roleIds)->get();
    }

    
}
