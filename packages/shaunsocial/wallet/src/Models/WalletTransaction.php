<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Models\User;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasSubject;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Wallet\Http\Resources\WalletTransactionResource;

class WalletTransaction extends Model
{    
    use HasSubject, IsSubject, HasUser, HasCacheQueryFields;

    protected $fillable = [
        'user_id',
        'type',
        'type_extra',
        'params',
        'amount',
        'from_user_id',
        'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    protected $cacheQueryFields = [
        'id'
    ];

    public function getId()
    {
        return $this->id;
    }
    
    public function getClass()
    {
        return  WalletTransactionType::getClassByType($this->type);
    }

    public function getDescription()
    {
        if ($this->getClass()) {
            $class = app($this->getClass());
            return $class->getDescription($this);
        }

        return '';
    }

    public function getFromUser()
    {
        if ($this->from_user_id) {
            return User::findByField('id', $this->from_user_id);
        }
    }

    public function getExtra()
    {
        if ($this->getClass()) {
            $class = app($this->getClass());
            return $class->getExtra($this);
        }

        return [];
    }

    public function getGross()
    {
        if ($this->getClass()) {
            $class = app($this->getClass());
            return $class->getGross($this);
        }
        return formatNumber($this->amount);
    }

    public function getFee()
    {
        if ($this->getClass()) {
            $class = app($this->getClass());
            return $class->getFee($this);
        }
        return '0';
    }

    public function getNet()
    {
        if ($this->getClass()) {
            $class = app($this->getClass());
            return $class->getNet($this);
        }
        return formatNumber($this->amount);
    }

    public function getTransactionId()
    {
        switch ($this->type) {
            case 'buy':
                $subject = $this->getSubject();
                return $subject->gateway_transaction_id;
                break;
        }

        return '';
    }

    public static function getCacheUserBalanceKey($userId)
    {
        return 'wallet_balance_'.$userId;
    }

    static public function getBalanceByUser($userId, $isCache = true)
    {
        $condition = self::where('user_id', $userId)->where('is_active', true);

        if ($isCache) {
            return Cache::remember(self::getCacheUserBalanceKey($userId), config('shaun_core.cache.time.model_query'), function () use ($condition) {
                return  $condition->sum('amount');
            });
        }

        return $condition->sum('amount');
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheUserBalanceKey($this->user_id));
    }

    public static function booted()
    {
        parent::booted();

        static::created(function ($transaction) {
            if ($transaction->amount < 0 && $transaction->user_id != config('shaun_wallet.system_wallet_user_id')) {
                WalletNotifyBalance::createRow($transaction->user_id);
            }
            $transaction->clearCache();
        });

        static::updated(function ($transaction) {
            $transaction->clearCache();
        });

        static::deleted(function ($transaction) {
            $transaction->clearCache();
        });
    }

    public static function getResourceClass()
    {
        return WalletTransactionResource::class;
    }

    public function getParams()
    {
        if ($this->params) {
            return json_decode($this->params, true);
        }

        return [];
    }
}
