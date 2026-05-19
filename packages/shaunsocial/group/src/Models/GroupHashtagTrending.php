<?php


namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Illuminate\Support\Facades\Cache;

class GroupHashtagTrending extends Model
{
    use Prunable;

    protected $fillable = [
        'hashtag_id',
        'name',
        'is_active',
        'post_id',
        'group_id'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public static function getByGroup($groupId)
    {
        return Cache::remember(self::getKeyCache($groupId), config('shaun_core.cache.time.model_query'), function () use ($groupId) {
            return self::where('is_active', true)->orderBy('count', 'DESC')->where('group_id', $groupId)->groupBy('hashtag_id')->selectRaw('hashtag_id, count(*) as count')->limit(config('shaun_group.hashtag_trending_limit'))->get();
        });
    }

    public static function getKeyCache($groupId)
    {
        return 'group_hashtag_trending_'.$groupId;
    }

    public static function clearCache($groupId)
    {
        Cache::forget(self::getKeyCache($groupId));
    }

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(setting('feature.hashtag_trending_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
