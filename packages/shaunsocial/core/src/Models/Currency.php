<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Currency extends Model
{
    protected $fillable = [
        'code',
        'is_default',
        'symbol',
        'name'
    ];

    protected $casts = [
        'is_default' => 'boolean',
    ];

    public function canDelete()
    {
        return ! $this->is_default;
    }

    static function getDefault()
    {
        return Cache::rememberForever('currency_default', function () {
            return self::where('is_default', true)->first();
        });
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($currency) {
            Cache::forget('currency_default');
        });

        static::saved(function ($currency) {
            Cache::forget('currency_default');
        });
    }
}
