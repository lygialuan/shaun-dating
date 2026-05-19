<?php 


namespace Packages\ShaunSocial\Core\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Core\Http\Requests\Country\GetCityValidate;
use Packages\ShaunSocial\Core\Http\Requests\Country\GetStateValidate;
use Packages\ShaunSocial\Core\Repositories\Api\CountryRepository;

class CountryController extends ApiController
{
    protected $countryRepository;

    public function getWhitelistForceLogin()
    {
        if (setting('feature.require_location')) {
            return [
                'get',
                'get_state',
                'get_city'
            ];
        }

        return [];
    }

    public function __construct(CountryRepository $countryRepository)
    {
        $this->countryRepository = $countryRepository;
        parent::__construct();
    }

    public function get(Request $request)
    {
        $result = $this->countryRepository->get();

        return $this->successResponse($result);
    }

    public function get_state(GetStateValidate $request)
    {
        $result = $this->countryRepository->get_state($request->country_id);

        return $this->successResponse($result);
    }

    public function get_city(GetCityValidate $request)
    {
        $result = $this->countryRepository->get_city($request->state_id);

        return $this->successResponse($result);
    }
}