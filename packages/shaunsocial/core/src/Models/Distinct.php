<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Illuminate\Database\Eloquent\Prunable;

class Distinct extends Model
{
    use HasUser, Prunable;

    public $timestamps = false;

    protected $fillable = [
        'type',
        'user_id',
        'subject_type',
        'subject_id',
        'updated_at',
        'hash',
        'user_hash'
    ];

    public static function getKeyCache($hash)
    {
        return 'distinct_'.$hash;
    }

    public static function getHash($data)
    {
        return md5($data['type'].'_'.$data['user_id'].'_'.$data['subject_type'].'_'.$data['subject_id']);
    }

    public static function getUserHash($data)
    {
        return md5($data['type'].'_'.$data['subject_type'].'_'.$data['subject_id']);
    }

    public static function getDistinct($data) 
    {
        $hash = self::getHash($data);
        return Cache::remember(self::getKeyCache($hash), config('shaun_core.cache.time.model_query'), function () use ($hash) {
            return self::where('hash', $hash)->first();
        });
    }

    public function prunable()
    {
        return self::where('updated_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->limit(setting('feature.item_per_page'));
    }

    public static function booted()
    {
        parent::booted();

        static::creating(function ($model) {
            $model->updated_at = now();
            $model->hash = self::getHash($model->toArray());
            $model->user_hash = self::getUserHash($model->toArray());
        });

        static::deleting(function ($model) {
            Cache::forget(self::getKeyCache($model->hash));
        });
    }
}
