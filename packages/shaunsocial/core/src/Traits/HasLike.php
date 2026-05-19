<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\Like;

trait HasLike
{
    public function initializeHasLike()
    {
        $this->fillable[] = 'like_count';
    }

    public function addLike($userId)
    {
        Like::create([
            'user_id' => $userId,
            'subject_type' => $this->getSubjectType(),
            'subject_id' => $this->id,
        ]);
    }

    public function getLikeCount()
    {
        return Like::where('subject_type', $this->getSubjectType())->where('subject_id', $this->id)->count();
    }

    public function getLike($userId)
    {
        return Like::getLike($userId, $this->getSubjectType(), $this->id);
    }

    public function supportLike()
    {
        return true;
    }

    public function sendLikeNotification($viewer)
    {
        
    }

    public function deleteLikeNotification($viewer)
    {
        
    }
    
    public static function bootHasLike()
    {
        static::created(function ($model) {
            $model->like_count = 0;
        });

        static::deleted(function ($model) {
            Like::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->delete();
        });
    }
}
