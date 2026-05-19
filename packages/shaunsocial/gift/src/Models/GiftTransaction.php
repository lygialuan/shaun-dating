<?php

namespace Packages\ShaunSocial\Gift\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Gift\Models\Gift;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;

class GiftTransaction extends Model
{
    use IsSubject, HasCachePagination;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'gift_id',
        'quantity',
        'total_price',
        'target_type',
        'target_id'
    ];

    protected $casts = [
        'sender_id' => 'integer',
        'receiver_id' => 'integer',
        'gift_id' => 'integer',
        'quantity' => 'integer',
        'total_price' => 'integer',
        'target_id' => 'integer',
    ];

    public function getListCachePagination()
    {
        return [
            'user_gift_received_'.$this->receiver_id,
        ];
    }

    public function getListFieldPagination()
    {
        return [
            'receiver_id',
        ];
    }
    
    public function gift()
    {
        return $this->belongsTo(Gift::class);
    }

    public function sender()
    {
        return $this->belongsTo(User::class, 'sender_id');
    }
}