<?php

namespace Packages\ShaunSocial\GatewayRecurring\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\UserSubscription\Models\UserSubscriptionPlan;

class GatewayRecurring extends Model
{
    use HasCacheQueryFields;
    
    public static $cachePermanentKey = 'gateway_recurrings';

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

    public function createLinkPayment($order, $flexFormId)
    {
        return $this->getClass()->createLinkPayment($order, $flexFormId);
    }

    public function doCancel($params)
    {
        return $this->getClass()->doCancel($params);
    }

    public function doResume($params)
    {
        return $this->getClass()->doResume($params);
    }
    
    static function getAll()
    {
        return Cache::rememberForever('GatewayRecurrings', function () {
            return self::where('is_active', true)->where('show', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    public function plans()
    {
        return $this->belongsToMany(
            UserSubscriptionPlan::class,
            'plan_gateway_recurrings',
            'gateway_recurring_id',
            'plan_id'
        )->withPivot('flex_form_id')->withTimestamps();
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($GatewayRecurring) {
            Cache::forget('GatewayRecurrings');
        });

        static::saved(function ($GatewayRecurring) {
            Cache::forget('GatewayRecurrings');
        });
    }
}
