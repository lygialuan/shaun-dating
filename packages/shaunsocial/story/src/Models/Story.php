<?php


namespace Packages\ShaunSocial\Story\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;

class Story extends Model
{
    use HasUser, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
        'user_id',
    ];
    
    protected $fillable = [
        'user_id',
        'user_privacy',
        'last_updated_at',
    ];

    protected $items = [];
    protected $itemLastSeens = [];

    public function getItems()
    {
        if (! count($this->items)) {
            $this->items = StoryItem::findByField('story_id', $this->id, true);
        }
        
        return $this->items;
    }

    public function canView($userId)
    {
        return $this->getUser()->canView($userId);
    }

    public function setItems($items)
    {
        $this->items = $items;
    }

    public function getItemLastSeen($userId)
    {
        if (! isset($this->itemLastSeens[$userId])) {
            $this->itemLastSeens[$userId] = StoryView::getLastSeen($userId, $this->id);
        }
        return $this->itemLastSeens[$userId];
    }

    public function getItemOnList($userId)
    {
        $lastItemSeenId = $this->getItemLastSeen($userId);
        $items = $this->getItems();
        $item = $items->first(function ($value, $key) use ($lastItemSeenId) {
            return $value->id > $lastItemSeenId;
        });

        if (! $item) {
            $item = $items->first();
        }

        return $item;
    }

    public function checkSeen($userId)
    {
        $lastItemSeenId = $this->getItemLastSeen($userId);
        
        $items = $this->getItems();        
        $item = $items->first(function ($value, $key) use ($lastItemSeenId) {
            return $value->id > $lastItemSeenId;
        });

        if (! $item) {
            return true;
        }

        if ($item->id != $lastItemSeenId) {
            return false;
        }

        return true;
    }

    public static function updatePrivacy($userId, $privacy)
    {
        $story = self::findByField('user_id', $userId);
        if ($story) {
            $story->update([
                'user_privacy' => $privacy
            ]);
        }
    }

    protected function scopeAttribute($builder, $attributes)
    {
        $builder->where(function ($query) use ($attributes) {
            foreach ($attributes as $group) {
                $query->where(function ($subQuery) use ($group) {
                    foreach ($group as $tagId) {
                        $subQuery->orWhereFullText('users.attributes', $tagId);
                    }
                });
            }
        });
    }

    protected function scopeInterestAttributes($builder, $attributes)
    {
        $builder->where(function ($query) use ($attributes) {
            foreach ($attributes as $group) {
                $query->where(function ($subQuery) use ($group) {
                    foreach ($group as $tagId) {
                        $subQuery->orWhereFullText('users.interest_attributes', $tagId);
                    }
                });
            }
        });
    }
}
