<?php

namespace Packages\ShaunSocial\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class GiftAggregate extends Model
{
    protected $table = 'gift_aggregates';

    protected $fillable = [
        'target_type',
        'target_id',
        'sender_id',
        'receiver_id',
        'total_gifts',
        'total_amount'
    ];

    protected $casts = [
        'target_id' => 'integer',
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'total_gifts' => 'integer',
        'total_amount' => 'integer'
    ];

    public static function getTotalReceived($receiverId)
    {
        return Cache::rememberForever('get_total_gift_received_' . $receiverId,function () use ($receiverId) {
            return self::where('receiver_id', $receiverId)->sum('total_gifts');
        });
    }

    public function clearCache()
    {
        Cache::forget('get_total_gift_received_'. $this->receiver_id);
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($gift) {
            $gift->clearCache();
        });

        static::saved(function ($gift) {
            $gift->clearCache();
        });
    }
}