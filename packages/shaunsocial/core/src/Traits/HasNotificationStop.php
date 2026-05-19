<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\UserNotificationStop;

trait HasNotificationStop
{
    public function addNotificationStop($userId)
    {
        UserNotificationStop::create([
            'user_id' => $userId,
            'subject_type' => $this->getSubjectType(),
            'subject_id' => $this->id,
        ]);
    }

    public function getNotificationStop($userId)
    {
        return UserNotificationStop::getNotificationStop($userId, $this->getSubjectType(), $this->id);
    }

    public function supportNotificationStop()
    {
        return true;
    }
    
    public static function bootHasNotificationStop()
    {
        static::deleted(function ($model) {
            UserNotificationStop::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->delete();
        });
    }
}
