<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WalletPaymentType extends Model
{ 
    protected $fillable = [
        'type',
        'class'
    ];

    static function getAll()
    {
        return Cache::rememberForever('wallet_payment_types', function () {
            return self::all();
        });
    }

    static public function getClassByType($type)
    {
        $types = self::getAll();
        $typeObject = $types->first(function ($value, $key) use ($type){
            return $value->type == $type;
        });

        if ($typeObject) {
            return $typeObject->class;
        }

        return '';
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($type) {
            Cache::forget('wallet_payment_types');
        });

        static::saved(function ($type) {
            Cache::forget('wallet_payment_types');
        });
    }
}
