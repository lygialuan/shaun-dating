<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasTranslations;

class TwoFactorProvider extends Model
{
    use HasTranslations, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id'
    ];

    protected $translatable = [
        'description',
        'name'
    ];

    protected $fillable = [
        'name',
        'description',
        'is_active',
        'type',
        'config'
    ];

    protected $casts = [
        'is_default' => 'boolean'
    ];

    public $timestamps = false;

    static function getAll()
    {
        return Cache::rememberForever('two_factor_providers', function () {
            return self::where('is_active', true)->get();
        });
    }

    static function getByType($type)
    {
        $providers = self::getAll();
        return $providers->first(function ($value, $key) use ($type) {
            return $type  == $value->type;
        });
    }

    public function getConfig()
    {
        return $this->config ? json_decode($this->config, true) : [];
    }

    public function clearCacheTranslate()
    {
        Cache::forget('two_factor_providers');
    }

    protected static function booted()
    {
        parent::booted();

        static::saved(function ($provider) {
            Cache::forget('two_factor_providers');
        });
    }
}
