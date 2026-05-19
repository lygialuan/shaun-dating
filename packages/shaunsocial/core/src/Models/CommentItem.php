<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;

class CommentItem extends Model
{
    use HasCacheQueryFields, HasSubject, Prunable;

    protected $cacheQueryFields = [
        'id',
        'comment_id',
    ];

    protected $fillable = [
        'user_id',
        'comment_id',
        'subject_type',
        'subject_id',
        'order',
    ];

    public function canStore($viewerId, $subjectType)
    {
        return $this->user_id == $viewerId && $this->comment_id == 0 && $this->subject_type == $subjectType;
    }

    public function canDelete($viewerId)
    {
        return $this->user_id == $viewerId && $this->comment_id == 0;
    }

    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($item) {
            $item->comment_id = 0;
        });

        static::deleted(function ($item) {
            $item->getSubject()->delete();
        });
    }

    public function prunable()
    {
        return static::where('comment_id',0)->where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
