<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\UserHashtag;

trait HasUserHashtag
{
    public static function bootHasUserHashtag()
    {
        static::deleted(function ($model) {
            $item = UserHashtag::getBySubject($model->getSubjectType(), $model->id);
            if ($item) {
                $item->delete();
            }
        });
        
        static::updated(function ($model) {
            $item = UserHashtag::getBySubject($model->getSubjectType(), $model->id);
            if ($item) {
                $item->update([
                    'hashtags' => $model->hashtags
                ]);
            }
        });

        static::created(function ($model) {
            UserHashtag::create([
                'user_id' => $model->user_id,
                'subject_type' => $model->getSubjectType(),
                'subject_id' => $model->id,
                'hashtags' => $model->hashtags
            ]);
        });
    }
}
