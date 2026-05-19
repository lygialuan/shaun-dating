<?php
namespace Packages\ShaunSocial\Dating\Repositories\Helpers;

use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Traits\Utility;
use Packages\ShaunSocial\Dating\Models\DatingAddress;
use Packages\ShaunSocial\Dating\Support\Facades\Dating;

class Package
{
    use Utility;

    public function install()
    {
        $this->installAddress();
    }

    public function installAddress() {
        $data = [];
        $countries = Country::all();
        foreach ($countries as $country) {
            $data[] = [
                'country_id' => $country->id,
                'address' => $country->name,
                'is_active' => $country->is_active,
                'state_id' => null,
                'city_id' => null
            ];
        }
        $states = State::all();
        foreach ($states as $state) {
            $isActive = $state->is_active;
            if ($state->is_active) {
                $country = $state->getCountry();
                $isActive = $country->is_active;
            }
            $data[] = [
                'country_id' => $state->country_id,
                'state_id' => $state->id,
                'city_id' => null,
                'is_active' => $isActive,
                'address' => Dating::makeAddress($state->country_id, $state->id, null)
            ];
        }

        $cities = City::all();
        foreach ($cities as $city) {
            $isActive = $city->is_active;
            if ($isActive) {
                $country = $city->getCountry();
                $isActive = $country->is_active;
                if ($isActive) {
                    $state = $city->getState();
                    $isActive = $state->is_active;
                }
            }

            $data[] = [
                'country_id' => $city->country_id,
                'state_id' => $city->state_id,
                'city_id' => $city->id,
                'is_active' => $isActive,
                'address' => Dating::makeAddress($city->country_id, $city->state_id, $city->id)
            ];
        }
        
        DatingAddress::insert($data);
    }
}
