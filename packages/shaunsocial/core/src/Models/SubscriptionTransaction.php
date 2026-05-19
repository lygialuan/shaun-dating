<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Enum\SubscriptionTransactionStatus;
use Packages\ShaunSocial\Core\Traits\HasCachePagination;

class SubscriptionTransaction extends Model
{
    use HasCachePagination;
    
    protected $fillable = [
        'user_id',
        'amount',
        'currency',
        'subscription_id',
        'params',
        'status',
        'gateway_transaction_id'
    ];

    protected $casts = [
        'status' => SubscriptionTransactionStatus::class,
    ];

    public function getListCachePagination()
    {
        return [
            'subscription_transaction_'.$this->subscription_id,
        ];
    }

    public function getPrice()
    {
        return formatNumber($this->amount). ' '.$this->currency;
    }

    public function getStatusText()
    {
        $status = SubscriptionTransactionStatus::getAll();
        return $status[$this->status->value];
    }

    public function getTransactionId()
    {
        return $this->gateway_transaction_id;
    }
}
