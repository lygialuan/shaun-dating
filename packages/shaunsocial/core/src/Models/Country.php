<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Dating\Models\DatingAddress;

class Country extends Model
{
    use HasTranslations, HasCacheQueryFields;

    protected $cacheQueryFields = [
        'id',
    ];

    protected $translatable = [
        'name'
    ];
    
    protected $fillable = [
        'name',
        'country_iso',
        'state_count',
        'order',
        'is_active'
    ];

    public static function getAll()
    {
        return Cache::rememberForever('countries', function () {
            return self::orderBy('order')->where('is_active', true)->orderBy('id','DESC')->get();
        });
    }

    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::forget('countries');
    }

    protected static function booted()
    {
        parent::booted();

        static::deleting(function ($country) {
            $country->clearCache();
            DatingAddress::deleteByCountry($country);

            $states = State::where('country_id', $country->id)->get();
            $states->each(function($state) {
                $state->delete();
            });
        });

        static::saved(function ($country) {
            $address = DatingAddress::where('country_id', $country->id)->get();
            $address->each(function ($address) use ($country) {
                $address->update(['is_active' => $country->is_active]);
            });
            if ($address->isEmpty()) {
                DatingAddress::storeByCountry($country);
            }
            $country->clearCache();
        });
    }
}

