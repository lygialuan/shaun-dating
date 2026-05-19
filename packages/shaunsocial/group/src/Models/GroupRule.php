<?php

namespace Packages\ShaunSocial\Group\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class GroupRule extends Model
{
    use HasCacheQueryFields;
    protected $number = null;

    protected $cacheQueryFields = [
        'group_id',
        'id'
    ];

    protected $fillable = [
        'title',
        'description',
        'group_id',
        'order'
    ];

    public static function getCacheByGroup($groupId)
    {
        return 'group_rule_'.$groupId;
    }

    public static function getByGroup($groupId)
    {
        return Cache::remember(self::getCacheByGroup($groupId), config('shaun_core.cache.time.model_query'), function () use ($groupId) {
            return self::where('group_id', $groupId)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheByGroup($this->group_id));
    }

    public function setNumber($number)
    {
        $this->number = $number;
    }

    public function getNumber()
    {
        return $this->number;
    }

    public static function booted()
    {
        parent::booted();

        static::saved(function ($rule) {
            $rule->clearCache();
        });

        static::deleted(function ($rule) {
            $rule->clearCache();
        });
    }
}