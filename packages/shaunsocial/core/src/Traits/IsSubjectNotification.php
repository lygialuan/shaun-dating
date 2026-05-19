<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\UserNotification;

trait IsSubjectNotification
{
    public static function bootIsSubjectNotification()
    {
        static::deleted(function ($model) {
            UserNotification::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->get()->each(function($notification){
                $notification->delete();
            });
        });
    }
}
