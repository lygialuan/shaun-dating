<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class SettingGroup extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
        'key'
    ];
    
    public $timestamps = false;

    protected $fillable = [
        'key',
        'name',
        'order'
    ];

    public function groupSubs()
    {
        return $this->hasMany(SettingGroupSub::class, 'group_id')->orderBy('order');
    }

    public static function booted()
    {
        static::saved(function ($group) {
            Cache::forget('admin_setting_groups');
        });
    }
}
