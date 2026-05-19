<?php


namespace Packages\ShaunSocial\Core\Repositories\Api;

use Packages\ShaunSocial\Core\Http\Resources\Country\CityResource;
use Packages\ShaunSocial\Core\Http\Resources\Country\CountryResource;
use Packages\ShaunSocial\Core\Http\Resources\Country\StateResource;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;

class CountryRepository
{
    public function get()
    {
        $countries = Country::getAll();

        return CountryResource::collection($countries);
    }

    public function get_state($countryId)
    {
        $states = State::getStateByCountryId($countryId);

        return StateResource::collection($states);
    }

    public function get_city($stateId)
    {
        $cities = City::getCitiesByStateId($stateId);

        return CityResource::collection($cities);
    }
}