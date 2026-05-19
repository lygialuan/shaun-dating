<?php
namespace Packages\ShaunSocial\Dating\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Packages\ShaunSocial\Core\Traits\HasCacheQueryFields;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;

class DatingAddress extends Model
{
    use HasCacheQueryFields;

    public $timestamps = true;

    protected $fillable = [
        'country_id',
        'state_id',
        'city_id',
        'address',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean'
    ];

    public static function storeByCity($city) {
        $state = State::findByField('id', $city->state_id);
        
        $newAddress = $city->name . ', '. $state->name;

        self::where('city_id', $city->id)->updateOrInsert([
            'country_id' => $city->country_id,
            'state_id' => $city->state_id,
            'city_id' => $city->id,
            'address' => $newAddress,
            'is_active' => $city->is_active
        ]);
    }

    public static function storeByState($state) {
        $country = Country::findByField('id', $state->country_id);
        $newAddress = $state->name;

        self::where('state_id', $state->id)
            ->whereNull('city_id')
            ->updateOrInsert([
                'country_id' => $country->id,
                'state_id' => $state->id,
                'address' => $newAddress,
                'is_active' => $state->is_active
            ]);

        $cities = City::where('state_id', $state->id)->get();

        foreach ($cities as $city) {
            self::storeByCity($city);
        }
    }

    public static function storeByCountry($country) {
        $newAddress = $country->name;

        self::where('country_id', $country->id)
            ->whereNull('state_id')
            ->whereNull('city_id')
            ->updateOrInsert([
                'country_id' => $country->id,
                'address' => $newAddress,
                'is_active' => $country->is_active
            ]);

        $states = State::where('country_id', $country->id)->get();

        foreach ($states as $state) {
            self::storeByState($state);
        }
    }

    public static function deleteByCountry($country) {
        $address = self::where('country_id', $country->id)->where('state_id', null)->where('city_id',null)->first();
        if ($address) {
            $address->delete();
        }
    }

    public static function deleteByState($state) {
        $address = self::where('state_id', $state->id)->where('city_id',null)->first();
        if ($address) {
            $address->delete();
        }
    }

    public static function deleteByCity($city) {
        $address = self::where('city_id', $city->id)->first();
        if ($address) {
            $address->delete();
        }
    }

    public static function findAddress($countryId, $stateId = null, $cityId = null) {
        $query = self::where('country_id', $countryId);

        if ($countryId) {
            $query->where('country_id', $countryId);
        }
        else {
            $query->whereNull('country_id');
        }

        if ($stateId) {
            $query->where('state_id', $stateId);
        }
        else {
            $query->whereNull('state_id');
        }

        if ($cityId) {
            $query->where('city_id', $cityId);
        }
        else {
            $query->whereNull('city_id');
        }

        return Cache::remember(
            self::getAddressCacheName($countryId, $stateId, $cityId), 
            config('shaun_dating.cache.time.30_min'),
            function () use ($query) {
                return $query->first();
            }
        );
    }

    public static function getAddressCacheName($countryId, $stateId = null, $cityId = null) {
        return "get_travel_address_country_id{$countryId}_state_id_{$stateId}_city_id_{$cityId}";
    }

    public static function getCacheNamePopularAddress() {
        return "get_popular_addresses_";
    }

    public static function getAll()
    {
        return Cache::rememberForever('listing_addresses', function () {
            return self::orderBy('id', 'ASC')->get();
        });
    }

    public function scopeActive($builder) {
        $builder->where('is_active', true);
    }

    protected static function booted()
    {
        parent::booted();
    }
}
