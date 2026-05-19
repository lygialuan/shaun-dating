<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class UserBlock extends Model
{
    use HasCachePagination, HasCacheQueryFields;

    protected $fillable = [
        'user_id',
        'blocker_id',
        'is_page'
    ];

    protected $casts = [
        'is_page' => 'boolean',
    ];

    protected $cacheQueryFields = [
        'user_id',
    ];

    public function getListCachePagination()
    {
        return [
            'block_'.$this->user_id,
            'block_user_'.$this->user_id,
            'block_page_'.$this->user_id
        ];
    }

    public static function getBlock($userId, $blockerId)
    {
        return Cache::remember(self::getCacheBlockKey($userId, $blockerId), config('shaun_core.cache.time.model_query'), function () use ($userId, $blockerId) {
            $block = self::where('user_id', $userId)->where('blocker_id', $blockerId)->first();

            return is_null($block) ? false : $block;
        });
    }

    public static function checkBlock($userId, $blockerId)
    {
        return Cache::remember(self::getCacheCheckBlockKey($userId, $blockerId), config('shaun_core.cache.time.model_query'), function () use ($userId, $blockerId) {
            $block = self::orWhere(function($query) use ($userId, $blockerId){
                $query->where('user_id', $userId)->where('blocker_id', $blockerId);
            })->orWhere(function($query) use ($userId, $blockerId){
                $query->where('user_id', $blockerId)->where('blocker_id', $userId);
            })->first();
            
            return is_null($block) ? false : true;
        });
    }

    public static function getBlocks($userId)
    {
        return Cache::remember(self::getCacheBlocksKey($userId), config('shaun_core.cache.time.model_query'), function () use ($userId) {
            $blocks = self::where('user_id', $userId)->orWhere('blocker_id', $userId)->get();

            return $blocks->map(function ($block, $key) use ($userId) {
                return $block->user_id == $userId ? $block->blocker_id : $block->user_id;
            })->toArray();
        });
    }

    public static function getCacheBlockKey($userId, $blockerId)
    {
        return 'block_check_'.$userId.'_'.$blockerId;
    }

    public static function getCacheCheckBlockKey($userId, $blockerId)
    {
        return 'block_check_'.$userId.'_'.$blockerId;
    }

    public static function getCacheBlocksKey($userId,)
    {
        return 'blocks_'.$userId;
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheCheckBlockKey($this->user_id, $this->blocker_id));
        Cache::forget(self::getCacheCheckBlockKey($this->blocker_id, $this->user_id));
        Cache::forget(self::getCacheBlocksKey($this->user_id));
        Cache::forget(self::getCacheBlocksKey($this->blocker_id));
        Cache::forget(self::getBlock($this->user_id, $this->blocker_id));
        Cache::forget(self::getBlock($this->blocker_id, $this->user_id));
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($block) {
            User::findByField('id', $block->user_id)->increment('block_count');

            $block->clearCache();
        });

        static::deleted(function ($block) {
            User::findByField('id', $block->user_id)->decrement('block_count');

            $block->clearCache();
        });
    }
}
