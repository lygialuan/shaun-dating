<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\Report;

trait HasReport
{
    public function getReport($userId)
    {
        return Report::getReport($userId, $this->getSubjectType(), $this->id);
    }

    public function getReportToUserId($userId = null)
    {
        return $this->user_id;
    }

    public function supportReport()
    {
        return true;
    }

    public function canReport($userId)
    {
        return true;
    }

    public static function bootHasReport()
    {
        static::deleted(function ($model) {
            Report::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->delete();
        });
    }
}
