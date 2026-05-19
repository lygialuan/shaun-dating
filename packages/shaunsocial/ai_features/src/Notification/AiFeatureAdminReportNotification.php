<?php

namespace Packages\ShaunSocial\AiFeatures\Notification;

use Packages\ShaunSocial\Core\Notification\BaseNotification;

class AiFeatureAdminReportNotification extends BaseNotification
{
    protected $type = 'ai_feature_admin_report';
    protected $has_group = false;

    public function getExtra()
    {
        return [
            'url' => $this->getHref()
        ];
    }

    public function getMessage($count)
    {
        return __('You got a report from AI Moderator.');
    }

    public function getHref()
    {
        return route('admin.report.index');
    }
}
