<?php


namespace Packages\ShaunSocial\Wallet\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class WalletTransactionType extends Model
{ 
    protected $fillable = [
        'type',
        'order',
        'class',
        'is_root'
    ];

    protected $casts = [
        'is_root' => 'boolean',
    ];

    static function getAll()
    {
        return Cache::rememberForever('wallet_transaction_types', function () {
            return self::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
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

    static function getAllValue($isRoot = false)
    {
        $result  = ['all' => __('All')];
        
        $types = self::getAll();
        foreach ($types as $type) {
            if (! $isRoot && $type->is_root) {
                continue;
            }
            $class = app($type->class);
            $result[$type->type] = $class->getName();
        }

        return $result;
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($type) {
            Cache::forget('wallet_transaction_types');
        });

        static::saved(function ($type) {
            Cache::forget('wallet_transaction_types');
        });
    }
}
