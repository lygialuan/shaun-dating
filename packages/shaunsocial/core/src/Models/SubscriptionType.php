<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Enum\SubscriptionTypeShow;

class SubscriptionType extends Model
{
    protected $fillable = [
        'type',
        'class',
        'order',
        'show'
    ];

    protected $casts = [
        'show' => SubscriptionTypeShow::class,
    ];

    static function getAll()
    {
        return Cache::rememberForever('subscription_types', function () {
            return self::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();
        });
    }

    static function getAllByUser($user)
    {
        $types= self::getAll();
        return ['all' => __('All type')] + $types->filter(function($item, $key) use ($user){
            $check = true;
            if ($item->show != SubscriptionTypeShow::ALL) {
                if ($user->isPage()) {
                    $check = $item->show == SubscriptionTypeShow::PAGE;
                } else {
                    $check = $item->show == SubscriptionTypeShow::USER;
                }
            }

            return $check;
        })->mapWithKeys(function($item, $key) use ($user){
            return [$item->type => $item->getClass()->getName()];
        })->all();
    }

    public function getClass()
    {
        return app($this->class);
    }

    static function getClassByType($type)
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
            Cache::forget('subscription_types');
        });

        static::saved(function ($package) {
            Cache::forget('subscription_types');
        });
    }
}
