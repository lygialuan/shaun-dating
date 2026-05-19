<?php


namespace Packages\ShaunSocial\Core\Models;

use Illuminate\Database\Eloquent\Model;
use Packages\ShaunSocial\Core\Traits\HasTranslations;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Dating\Models\DatingAddress;

class State extends Model
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
        'state_code',
        'country_id',
        'order',
        'is_active',
        'city_count'
    ];

    public static function getStateByCountryId($countryId)
    {
        return Cache::rememberForever(self::getCacheCountryKey($countryId), function () use ($countryId)  {
            return self::where('country_id', $countryId)->where('is_active', true)->orderBy('order')->orderBy('id','DESC')->get();
        });
    }

    public function getCountry()
    {
        return Country::findByField('id', $this->country_id);
    }
    
    public static function getCacheCountryKey($countryId)
    {
        return 'states_country_'.$countryId;
    }
    
    public function clearCacheTranslate()
    {
        $this->clearCache();
    }

    public function clearCache()
    {
        Cache::forget(self::getCacheCountryKey($this->country_id));
    }

    protected static function booted()
    {
        parent::booted();

        static::creating(function ($state) {
            //increase state count of country
            $country = $state->getCountry();
            $country->update([
                'state_count' => $country->state_count + 1
            ]);

            $state->clearCache();
        });

        static::deleting(function ($state) {
            //decrease state count of country
            $country = $state->getCountry();
            $country->update([
                'state_count' => $country->state_count - 1
            ]);
            DatingAddress::deleteByState($state);
            $cities = City::where('state_id', $state->id)->get();
            $cities->each(function($city) {
                $city->delete();
            });
            
            $state->clearCache();
        });

        static::saved(function ($state) {
            $address = DatingAddress::where('state_id', $state->id)->get();
            if ($address->isEmpty()) {
                DatingAddress::storeByState($state);
            }
            $state->clearCache();
        });
    }

    public function delete_state($id){
        $state = State::findOrFail($id);

        $state->delete();

        return redirect()->back()->with([
            'admin_message_success' => __('State has been deleted.'),
        ]);
    }
}