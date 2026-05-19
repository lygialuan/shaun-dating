<?php


namespace Packages\ShaunSocial\PaidContent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class UserPostPaid extends Model
{
    use HasCacheQueryFields, Prunable, HasCachePagination;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'post_id',
        'order_id',
    ];

    public function getListCachePagination()
    {
        return [
            'user_post_paid_'.$this->user_id,
        ];
    }

    public static function getPaid($userId, $postId)
    {
        if (! $userId) {
            return false;
        }

        return Cache::remember(self::getKeyCache($userId, $postId), config('shaun_core.cache.time.model_query'), function () use ($userId, $postId) {
            $paid = self::where('user_id', $userId)->where('post_id', $postId)->first();

            return is_null($paid) ? false : $paid;
        });
    }

    public static function getKeyCache($userId, $postId)
    {
        return 'post_paid _'.$userId.'_'.$postId;
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($paid) {
            Cache::forget(self::getKeyCache($paid->user_id, $paid->post_id));
        });
    }
}
