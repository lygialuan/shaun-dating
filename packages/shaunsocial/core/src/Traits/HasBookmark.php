<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\Bookmark;

trait HasBookmark
{
    public function addBookmark($userId)
    {
        Bookmark::create([
            'user_id' => $userId,
            'subject_type' => $this->getSubjectType(),
            'subject_id' => $this->id,
        ]);
    }

    public function getBookmark($userId)
    {
        return Bookmark::getBookmark($userId, $this->getSubjectType(), $this->id);
    }

    public function supportBookmark()
    {
        return true;
    }

    public static function bootHasBookmark()
    {
        static::deleted(function ($model) {
            Bookmark::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->get()->each(function ($bookmark) {
                $bookmark->delete();
            });
        });
    }
}
