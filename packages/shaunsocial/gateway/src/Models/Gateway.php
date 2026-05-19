<?php

namespace Packages\ShaunSocial\Gateway\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;

class Gateway extends Model
{
    use HasCacheQueryFields;

    protected $cacheQueryFields = [
        'key',
        'id'
    ];

    protected $fillable = [
        'name',
        'key',
        'class',
        'config',
        'show',
        'is_active',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'show' => 'boolean'
    ];

    public function getClass()
    {
        return app($this->class);
    }

    public function getConfig()
    {
        if ($this->config) {
            return json_decode($this->config,true);
        }

        return [];
    }

    public function checkSupportCurrency($currencyCode) 
    {
        return $this->getClass()->checkSupportCurrency($currencyCode);
    }

    public function createLinkPayment($order)
    {
        return $this->getClass()->createLinkPayment($order);
    }

    static function getAll()
    {
        return Cache::rememberForever('gateways', function () {
            return self::where('is_active', true)->where('show', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($gateway) {
            Cache::forget('gateways');
        });

        static::saved(function ($gateway) {
            Cache::forget('gateways');
        });
    }
}
