<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Core\Traits\IsSubject;
use Packages\ShaunSocial\Wallet\Enum\WalletWithdrawStatus;

class WalletWithdraw extends Model
{
    use HasCacheQueryFields, HasUser, IsSubject;
    
    protected $cacheQueryFields = [
        'id'
    ];
    
    protected $fillable = [
        'user_id',        
        'amount',
        'fee',
        'type',
        'currency',
        'exchange',
        'bank_account',
        'status'
    ];
    
    protected $casts = [
        'status' => WalletWithdrawStatus::class
    ];

    public function canAccept()
    {
        return $this->status == WalletWithdrawStatus::INIT;
    }

    public function canReject()
    {
        return $this->status == WalletWithdrawStatus::INIT;
    }

    static public function getAllType()
    {
        return [
            'paypal' => __('Transfer to Paypal'),
            'bank' => __('Transfer to bank account'),
        ];
    }

    public function getGross($inAdmin = false)
    {
        $amount = formatNumber($this->amount/$this->exchange);
        $result = $amount.' '.$this->currency;
        if ($inAdmin) {
            return $result;
        } else {
            return '-'.$result;
        }
    }

    public function getFee($inAdmin = false)
    {
        $amount = formatNumber($this->fee);
        $result = $amount.' '.$this->currency;
        if ($inAdmin) {
            return $result;
        } else {
            return '-'.$result;
        }
    }

    public function getAmountNet()
    {
        return formatNumber($this->amount/$this->exchange - $this->fee);
    }

    public function getNet($inAdmin = false)
    {
        $amount = $this->getAmountNet();
        $result = $amount.' '.$this->currency;
        if ($inAdmin) {
            return $result;
        } else {
            return '-'.$result;
        }
    }

    public function getPaymentMethod()
    {
        $typeArray = $this->getAllType();

        return $typeArray[$this->type];
    }

    public function getExchangeInfo()
    {
        return '1 '.getWalletTokenName().' - '.(1 / $this->exchange).' '.$this->currency;
    }

    public function getAmountInfo()
    {
        return formatNumber($this->amount).' '.getWalletTokenName().' - '.formatNumber($this->amount/$this->exchange).' '.$this->currency;
    }

    public function getStatusText()
    {
        $status = WalletWithdrawStatus::getAll();
        
        return $status[$this->status->value];
    }
}
