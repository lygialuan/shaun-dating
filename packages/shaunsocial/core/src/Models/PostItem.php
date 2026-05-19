<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;

class PostItem extends Model
{
    use HasCacheQueryFields, HasSubject, Prunable;

    protected $cacheQueryFields = [
        'id',
        'post_id',
    ];

    protected $fillable = [
        'user_id',
        'post_id',
        'post_queue_id',
        'subject_type',
        'subject_id',
        'has_queue',
        'order',
    ];

    protected $casts = [
        'has_queue' => 'boolean',
    ];

    public function canStore($viewerId, $subjectType, $postId = 0)
    {
        return $this->user_id == $viewerId && ($this->post_id == 0 || $this->post_id == $postId) && $this->post_queue_id == 0 && $this->subject_type == $subjectType;
    }

    public function checkNeedQueue()
    {
        return $this->has_queue;
    }

    public function canDelete($userId)
    {
        return $this->user_id == $userId && $this->post_id == 0 && $this->post_queue_id == 0;
    }

    public function getOgImage()
    {
        $subject = $this->getSubject();
        if ($subject) {
            return $subject->getOgImage();
        }
        
        return '';
    }

    public function runQueue()
    {
        if ($this->checkNeedQueue()) {
            $result = $this->getSubject()->runQueue($this->user_id);
            if ($result) {
                $this->update([
                    'has_queue' => false
                ]);
            }
            return $result;
        }
    }

    protected static function booted()
    {
        parent::booted();
        
        static::created(function ($item) {
            $item->post_id = 0;
        });
        
        static::deleted(function ($item) {
            $subject = $item->getSubject();
            if ($subject->canDeleteSubject()) {
                $subject->delete();
            }
        });
    }

    public function prunable()
    {
        return static::where('post_id', 0)->where('post_queue_id', 0)->where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
