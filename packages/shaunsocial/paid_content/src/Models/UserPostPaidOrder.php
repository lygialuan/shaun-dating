<?php


namespace Packages\ShaunSocial\PaidContent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Prunable;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\PaidContent\Enum\UserPostPaidOrderStatus;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Gateway\Models\OrderBase;

class UserPostPaidOrder extends Model implements OrderBase
{
    use HasCacheQueryFields, Prunable, IsSubject, HasUser;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $fillable = [
        'user_id',
        'post_id',
        'post_owner_id',
        'amount',
        'status',
        'gateway_id',
        'params',
        'gateway_transaction_id',
        'currency',
    ];

    protected $casts = [
        'status' => UserPostPaidOrderStatus::class
    ];

    public function getPostOwner()
    {
        return User::findByField('id', $this->post_owner_id);
    }

    public function getParams()
    {
        if ($this->params) {
            return json_decode($this->params, true);
        }

        return [];
    }

    public function onSuccessful($data, $transactionId)
    {
        if ($this->status == UserPostPaidOrderStatus::DONE) {
            return;
        }

        $this->update([
            'status' => UserPostPaidOrderStatus::DONE,
            'params' => json_encode($data),
            'gateway_transaction_id' => $transactionId
        ]);

        UserPostPaid::create([
            'user_id' => $this->user_id,
            'post_id' => $this->post_id,
            'order_id' => $this->id
        ]);
    }

    public function getItems()
    {
        return [
            [
                'name' => __('Paid content'),
                'quantity' => 1,
                'amount' => $this->amount,
                'currency' => $this->currency
            ]
        ];
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function getTotalAmount()
    {
        return $this->amount;
    }

    public function getReturnUrl()
    {
        return route('web.wallet.index');
    }
    
    public function getCancelUrl()
    {
        return route('web.wallet.index');
    }
    
    public function prunable()
    {
        return static::where('created_at', '<', now()->subDays(config('shaun_core.core.auto_delete_day')))->where('status', UserPostPaidOrderStatus::INIT)->limit(setting('feature.item_per_page'));
    }
}
