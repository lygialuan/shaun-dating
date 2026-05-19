<?php


namespace Packages\ShaunSocial\Advertising\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Advertising\Enum\AdvertisingStatisticType;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class AdvertisingStatistic extends Model
{
    use HasCacheQueryFields;

    protected $fillable = [
        'user_id',
        'advertising_id',
        'type',
        'hash'
    ];

    protected $cacheQueryFields = [
        'hash',
    ];

    protected $casts = [
        'type' => AdvertisingStatisticType::class
    ];

    static public function add($data)
    {
        $hash = null;
        if ($data['type'] == AdvertisingStatisticType::CLICK) {
            if ($data['user_id']) {
                $hash = md5($data['advertising_id'].$data['user_id'].$data['type']->value);
            } else {
                $hash = md5($data['advertising_id'].$data['ip'].$data['type']->value);
            }
            
            if (self::findByField('hash', $hash)) {
                return;
            }
        }
        $data['hash'] = $hash;

        self::create($data);
    }

    public function prunable()
    {
        return self::where('created_at', '<', now()->subDays(config('shaun_advertising.day_delete_static')))->limit(setting('feature.item_per_page'));
    }
}
