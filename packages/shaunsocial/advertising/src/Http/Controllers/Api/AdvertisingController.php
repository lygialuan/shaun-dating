<?php


namespace Packages\ShaunSocial\Advertising\Http\Controllers\Api;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\GetDetailAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\StoreAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\StoreBootAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\StoreCompleteAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\StoreEditAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\StoreEnableAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\StoreStopAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\ValidateStoreAdvertisingValidate;
use Packages\ShaunSocial\Advertising\Http\Controllers\Requests\ValidateStoreBootAdvertisingValidate;
use Packages\ShaunSocial\Core\Http\Controllers\ApiController;
use Packages\ShaunSocial\Advertising\Repositories\Api\AdvertisingRepository;
use Packages\ShaunSocial\Core\Exceptions\MessageHttpException;
use Packages\ShaunSocial\Core\Http\Resources\Utility\GenderResource;
use Packages\ShaunSocial\Core\Models\Gender;

class AdvertisingController extends ApiController
{
    protected $advertisingRepository;

    public function __construct(AdvertisingRepository $advertisingRepository)
    {
        if (! setting('shaun_advertising.enable')) {
            throw new MessageHttpException(__('Do not support this method.'));
        }

        $this->advertisingRepository = $advertisingRepository;
        parent::__construct();
    }

    public function validate_store(ValidateStoreAdvertisingValidate $request)
    {
        return $this->successResponse();
    }

    public function store(StoreAdvertisingValidate $request)
    {
        $countryData = getCountryData();
        $request->mergeIfMissing([
            'id' => 0,
            'content' => '',
            'items' => [],
        ] + $countryData);

        $dataKey = array_merge(['type', 'content', 'items', 'hashtags', 'gender_id', 'age_from', 'age_to', 'age_type'],array_keys($countryData));
        if (! $request->input('id')) {
            $dataKey = array_merge($dataKey, ['name', 'start', 'end', 'daily_amount']);
        }
        $data = $request->only($dataKey);
        $data = cleanCountryData($data);
        if (empty($data['gender_id'])) {
            $data['gender_id'] = 0;
        }
        $result = $this->advertisingRepository->store($data, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function store_edit(StoreEditAdvertisingValidate $request)
    {
        $countryData = getCountryData();
        $request->mergeIfMissing([
            'id' => 0,
            'content' => '',
            'items' => [],
        ] + $countryData);

        $dataKey = array_merge(['id', 'type', 'content', 'items', 'hashtags', 'gender_id', 'age_from', 'age_to', 'age_type', 'name'],array_keys($countryData));
        $data = $request->only($dataKey);
        $data = cleanCountryData($data);
        if (empty($data['gender_id'])) {
            $data['gender_id'] = 0;
        }
        $result = $this->advertisingRepository->store($data, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function validate_store_boot(ValidateStoreBootAdvertisingValidate $request)
    {
        return $this->successResponse();
    }

    public function store_boot(StoreBootAdvertisingValidate $request)
    {
        $countryData = getCountryData();
        $request->mergeIfMissing($countryData);

        $dataKey = array_merge(['id', 'name', 'start', 'end', 'daily_amount', 'hashtags', 'gender_id', 'age_from', 'age_to', 'age_type'],array_keys($countryData));
        $data = $request->only($dataKey);
        $data = cleanCountryData($data);
        if (empty($data['gender_id'])) {
            $data['gender_id'] = 0;
        }
        $result = $this->advertisingRepository->store_boot($data, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function store_stop(StoreStopAdvertisingValidate $request)
    {
        $this->advertisingRepository->store_stop($request->id, $request->user());
        
        return $this->successResponse();
    }

    public function store_enable(StoreEnableAdvertisingValidate $request)
    {
        $this->advertisingRepository->store_enable($request->id, $request->user());

        return $this->successResponse();
    }

    public function config(Request $request)
    {
        $genders = Gender::getAll();
        return $this->successResponse([
            'genders' => GenderResource::collection($genders)
        ]);
    }
    
    public function store_complete(StoreCompleteAdvertisingValidate $request)
    {
        $result = $this->advertisingRepository->store_complete($request->id, $request->user());

        if ($result['status']) {
            return $this->successResponse();
        } else {
            return $this->errorMessageRespone($result['message']);
        }
    }

    public function get(Request $request)
    {
        $page = $request->page ? $request->page : 1;
        $status = $request->status;

        $result = $this->advertisingRepository->get($page, $status, $request->user());

        return $this->successResponse($result);
    }

    public function get_detail(GetDetailAdvertisingValidate $request)
    {
        $result = $this->advertisingRepository->get_detail($request->id);

        return $this->successResponse($result);
    }

    public function get_report(GetDetailAdvertisingValidate $request)
    {
        $page = $request->page ? $request->page : 1;
        $result = $this->advertisingRepository->get_report($request->id, $page);

        return $this->successResponse($result);
    }
}
