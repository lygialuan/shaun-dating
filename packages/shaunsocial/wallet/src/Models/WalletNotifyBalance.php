<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasUser;
use Packages\ShaunSocial\Wallet\Enum\WalletNotifyBalanceType;

class WalletNotifyBalance extends Model
{    
    use HasUser;
    
    protected $fillable = [
        'user_id',
        'type'
    ];

    protected $casts = [
        'type' => WalletNotifyBalanceType::class
    ];

    static public function createRow($userId, $type = null)
    {
        if (! $type) {
            $type = WalletNotifyBalanceType::REDUCE;
        }
        if (! self::where('user_id', $userId)->where('type', $type)->first()){
            self::create([
                'user_id' => $userId,
                'type' => $type
            ]);
        }
    }
}
