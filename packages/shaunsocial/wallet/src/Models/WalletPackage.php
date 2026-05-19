<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasDeleted;

class WalletPackage extends Model
{
    use HasDeleted, HasCacheQueryFields;
    
    protected $cacheQueryFields = [
        'id',
        'google_price_id',
        'apple_price_id'
    ];
    
    protected $fillable = [
        'amount',
        'google_price_id',
        'apple_price_id',
        'is_active',
        'is_delete',
        'order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    static function getAll()
    {
        return Cache::rememberForever('wallet_packages', function () {
            return self::where('is_active', true)->orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($package) {
            Cache::forget('wallet_packages');
        });

        static::saved(function ($package) {
            Cache::forget('wallet_packages');
        });
    }
}
