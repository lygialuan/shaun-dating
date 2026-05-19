<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Support\Facades\Notification;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Gateway\Models\OrderBase;
use Packages\ShaunSocial\Wallet\Enum\WalletNotifyBalanceType;
use Packages\ShaunSocial\Wallet\Enum\WalletOrderStatus;
use Packages\ShaunSocial\Wallet\Notification\WalletDepositDoneNotification;

class WalletOrder extends Model implements OrderBase
{    
    use HasCacheQueryFields, IsSubject, HasUser;

    protected $cacheQueryFields = [
        'id',
        'gateway_transaction_id'
    ];

    protected $fillable = [
        'user_id',
        'gateway_id',
        'amount',
        'currency',
        'status',
        'exchange',
        'package_id',
        'params',
        'gateway_transaction_id'
    ];

    protected $casts = [
        'status' => WalletOrderStatus::class
    ];

    public function onSuccessful($data, $transactionId)
    {
        if ($this->status == WalletOrderStatus::DONE) {
            return;
        }

        $this->update([
            'status' => WalletOrderStatus::DONE,
            'params' => json_encode($data),
            'gateway_transaction_id' => $transactionId
        ]);

        $transaction = WalletTransaction::findSubject($this);
        if ($transaction) {
            $transaction->update([
                'is_active' => true
            ]);

            WalletNotifyBalance::createRow($this->user_id, WalletNotifyBalanceType::ADD);
            
            //add transaction to admin
            WalletTransaction::create([
                'user_id' => config('shaun_wallet.system_wallet_user_id'),
                'type' => 'root_buy',
                'amount' => - ($this->amount * $this->exchange),
                'is_active' => true,
                'subject_type' => $transaction->getSubjectType(),
                'subject_id' => $transaction->id,
                'from_user_id' => $this->user_id
            ]);
        }

        $user = $this->getUser();
        if ($user) {
            Notification::send($user, $user, WalletDepositDoneNotification::class, null, ['is_system' => true], 'shaun_wallet', false);
        }
    }

    public function getItems()
    {
        return [
            [
                'name' => __('Buy').' '. round($this->amount * $this->exchange, 2) .'('. getWalletTokenName(). ')',
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

    public static function booted()
    {
        parent::booted();

        static::creating(function ($order) {
            if (! $order->package_id) {
                $order->package_id = 0;
            }
        });

        static::created(function ($order) {
            WalletTransaction::create([
                'user_id' => $order->user_id,
                'type' => 'buy',
                'amount' => $order->amount * $order->exchange,
                'is_active' => false,
                'subject_type' => $order->getSubjectType(),
                'subject_id' => $order->id
            ]);
        });
    }
}
