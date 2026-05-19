<?php


namespace Packages\ShaunSocial\Core\Traits;

use Packages\ShaunSocial\Core\Models\Comment;

trait HasComment
{
    public function initializeHasComment()
    {
        $this->fillable[] = 'comment_count';
        $this->fillable[] = 'reply_count';
    }

    public function supportComment()
    {
        return true;
    }

    public function sendCommentNotification($viewer, $comment)
    {
        
    }

    public function getCommentCount()
    {
        return Comment::where('subject_type', $this->getSubjectType())->where('subject_id', $this->id)->count();
    }

    public function getReplyCount()
    {
        return Comment::where('subject_type', $this->getSubjectType())->where('subject_id', $this->id)->sum('reply_count');
    }

    public static function bootHasComment()
    {
        static::created(function ($model) {
            $model->comment_count = 0;
        });

        static::deleted(function ($model) {
            Comment::where('subject_type', $model->getSubjectType())->where('subject_id', $model->id)->delete();
        });
    }
}
