<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Enum\PostPaidType;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSource;
use Packages\ShaunSocial\Core\Traits\HasStorageFiles;
use Packages\ShaunSocial\Core\Traits\HasUser;

class PostQueue extends Model
{
    use HasCacheQueryFields, Prunable, HasUser, HasSource, HasStorageFiles;
    protected $can_delete_item = true;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'content',
        'comment_privacy',
        'content_warning_categories',
        'type',
        'source_type',
        'source_id',
        'thumb_file_id',
        'is_paid',
        'content_amount',
        'paid_type'
    ];

    protected $casts = [
        'is_paid' => 'boolean',
        'paid_type' => PostPaidType::class
    ];

    protected $storageFields = [
        'thumb_file_id'
    ];

    public function setStorageFields($storageFields)
    {
        $this->storageFields = $storageFields;
    }

    public function getItems()
    {
        return PostItem::findByField('post_queue_id', $this->id, true);
    }
    
    protected static function booted()
    {
        parent::booted();
        
        static::deleted(function ($post) {
            if ($post->can_delete_item) {
                $items = $post->getItems();
                foreach ($items as $item) {
                    $item->delete();
                }
            }
        });
    }

    public function setCanDeleteItem($boolean)
    {
        $this->can_delete_item = $boolean;
    }

    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
