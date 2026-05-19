<?php


namespace Packages\ShaunSocial\Advertising\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class AdvertisingDelivery extends Model
{
    use HasCacheQueryFields;

    protected $fillable = [
        'key',
        'ids'
    ];

    protected $cacheQueryFields = [
        'key',
    ];

    public function getIds()
    {
        if ($this->ids) {
            return json_decode($this->ids, true);
        }

        return [];
    }

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }
}
