<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WalletTransactionSubType extends Model
{ 
    protected $fillable = [
        'parent_type_id',
        'type',
        'class'
    ];

    public static function getSubByType($type)
    {
        $types = WalletTransactionType::getAll();
        $typeItem = $types->first(function ($value, $key)  use ($type) {
            return $value->type == $type;
        });
        $typeId = $typeItem->id;
        
        return Cache::rememberForever('wallet_transaction_sub_type_'.$typeId, function ()  use ($typeId) {
            return self::where('parent_type_id', $typeId)->get();
        });
    }

    public static function getClassByType($type, $subType)
    {
        $types = self::getSubByType($type);
        $typeObject = $types->first(function ($value, $key) use ($subType){
            return $value->type == $subType;
        });

        if ($typeObject) {
            return $typeObject->class;
        }

        return '';
    }

    public static function getObjectClassByType($type, $subType)
    {
        $class = self::getClassByType($type, $subType);
        return app($class);
    }
}
