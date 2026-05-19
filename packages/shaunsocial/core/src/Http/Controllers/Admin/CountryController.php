<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Validation\FileValidation;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\Dating\Models\DatingAddress;

class CountryController extends Controller
{
    public function __construct()
    {
       $this->middleware('has.permission:admin.country.manage');
    }

    public function index(Request $request) {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Countries'),
            ],
        ];

        $title = __('Countries');
        $countries = Country::orderBy('order')->orderBy('id','DESC')->get();
        
        return view('shaun_core::admin.country.index', compact('breadcrumbs', 'title', 'countries'));
    }

    public function create($id = null) {

        if (empty($id)) {
            $country = new Country();
            $country->order = 0;
            $country->is_active = true;
        } else {
            $country = Country::findOrFail($id);
        }

        $title = empty($id) ? __('Create Country') : __('Edit Country');

        return view('shaun_core::admin.country.create', compact('title', 'country'));
    }

    public function store(Request $request) {
        $request->mergeIfMissing([
            'is_active' => 0
        ]);

        $rules = [
            'country_iso' => ['required'],
            'name' => 'required'
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'country_iso.required' => __('The ISO is required.'),
                'name.required' => __('The name is required.')
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false
        ]);

        $data = $request->except('id', '_token');
        if (empty($data['order'])) {
            $data['order'] = 0;
        }

        if ($request->id) {
            $country = Country::findOrFail($request->id);
            $country->update($data);
        } else {
            $country = Country::create($data);
        }

        if ($request->id) {
            $request->session()->flash('admin_message_success', __('Country has been updated.'));
        } else {
            $request->session()->flash('admin_message_success', __('Country has been created.'));
        }

        DatingAddress::storeByCountry($country);

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id) {
        $country = Country::findOrFail($id);

        DatingAddress::deleteByCountry($country);

        $country->delete();

        return redirect()->back()->with([
            'admin_message_success' => __('Country has been deleted.'),
        ]);
    }

    public function store_order(Request $request) {
        $rules = [
            'orders' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $country = Country::find($id);
            if (! $country) {
                continue;
            }
            $country->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function state($countryId){
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Countries'),
                'route' => 'admin.country.index',
            ],
            [
                'title' => __('States'),
            ],
        ];

        $title = __('States');

        $states = State::where('country_id', $countryId)->orderBy('order')->orderBy('id','DESC')->get();
        
        return view('shaun_core::admin.country.state', compact('breadcrumbs', 'title', 'countryId', 'states'));
    }

    public function create_state($countryId, $id = null) {
        if (empty($id)) {
            $state = new State();
            $state->order = 0;
            $state->is_active = true;
        } else {
            $state = State::findOrFail($id);
        }

        $country = Country::findOrFail($countryId);

        $title = empty($id) ? __('Create State') : __('Edit State');

        return view('shaun_core::admin.country.create_state', compact('title', 'state', 'country'));
    }
    
    public function store_state(Request $request){
        $request->mergeIfMissing([
            'is_active' => 0
        ]);

        $rules = [
            'name' => 'required'
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.')
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false
        ]);

        $data = $request->except('id', '_token');

        if (empty($data['order'])) {
            $data['order'] = 0;
        }

        if ($request->id) {
            $state = State::findOrFail($request->id);
            $state->update($data);
        } else {
            $state = State::create($data);
        }

        if ($request->id) {
            $request->session()->flash('admin_message_success', __('State has been updated.'));
        } else {
            $request->session()->flash('admin_message_success', __('State has been created.'));
        }

        DatingAddress::storeByState($state);

        return response()->json([
            'status' => true,
        ]);
    }

    public function import_state($countryId) 
    {
        $country = Country::findOrFail($countryId);
        return view('shaun_core::admin.country.import_state', compact('countryId'));
    }

    public function store_import_state($countryId, Request $request){
        $rules = [
            'file' => ['required', new FileValidation('csv')],
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        
        $content = $request->file;

        $file = fopen($content, 'r');
        $states = array();
        while (!feof($file)) {
            $state = fgetcsv($file, 0, ',');
            if (isset($state[0])) {
                $states[] = trim($state[0]);
            }
        }
        fclose($file);

        if (count($states)) {
            foreach ($states as $state) {
                $state = State::create([
                    'name' => $state,
                    'country_id' => $countryId
                ]);

                DatingAddress::storeByState($state);
            }
        }
       
        $request->session()->flash('admin_message_success', __('States has been successfully imported.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function store_state_order(Request $request) {
        $rules = [
            'orders' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $state = State::find($id);
            if (! $state) {
                continue;
            }
            $state->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_state($id){
        $state = State::findOrFail($id);

        DatingAddress::deleteByState($state);

        $state->delete();

        return redirect()->back()->with([
            'admin_message_success' => __('State has been deleted.'),
        ]);
    }

    public function city($stateId){
        $state = State::findOrFail($stateId);

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Countries'),
                'route' => 'admin.country.index',
            ],
            [
                'title' => __('States'),
                'route' => 'admin.country.state.index',
                'data' => [
                    'country_id' => $state->country_id
                ]

            ],
            [
                'title' => __('Cities'),
            ],
        ];

        $title = __('Cities');

        $cities = City::where('state_id', $stateId)->orderBy('order')->orderBy('id','DESC')->get();
        
        return view('shaun_core::admin.country.city', compact('breadcrumbs', 'title', 'state', 'cities'));
    }

    public function create_city($stateId, $id = null) {
        if (empty($id)) {
            $city = new City();
            $city->order = 0;
            $city->is_active = true;
        } else {
            $city = City::findOrFail($id);
        }

        $state = State::findOrFail($stateId);

        $title = empty($id) ? __('Create City') : __('Edit City');

        return view('shaun_core::admin.country.create_city', compact('title', 'state', 'city'));
    }

    public function store_city(Request $request){
        $request->mergeIfMissing([
            'is_active' => 0
        ]);
        
        $rules = [
            'name' => 'required'
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.')
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $request->mergeIfMissing([
            'is_active' => false
        ]);
        
        $data = $request->except('id', '_token');

        if (empty($data['order'])) {
            $data['order'] = 0;
        }

        if ($request->id) {
            $city = City::findOrFail($request->id);
            $city->update($data);
        } else {
            $city = City::create($data);
        }

        if ($request->id) {
            $request->session()->flash('admin_message_success', __('City has been updated.'));
        } else {
            $request->session()->flash('admin_message_success', __('City has been created.'));
        }

        DatingAddress::storeByCity($city);

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_city($id){
        $city = City::findOrFail($id);

        DatingAddress::deleteByCity($city);

        $city->delete();

        return redirect()->back()->with([
            'admin_message_success' => __('City has been deleted.'),
        ]);
    }

    public function store_city_order(Request $request) {
        $rules = [
            'orders' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        $orders = $request->orders;
        foreach ($orders as $order => $id) {
            $city = City::find($id);
            if (! $city) {
                continue;
            }
            $city->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }

    public function import_city($stateId) {
        State::findOrFail($stateId);
        return view('shaun_core::admin.country.import_city', compact('stateId'));
    }

    public function store_import_city($stateId, Request $request){
        $rules = [
            'file' => ['required', new FileValidation('csv')],
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'file.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }
        
        $state = State::findOrFail($stateId);
        $content = $request->file;

        $file = fopen($content, 'r');
        $cities = array();
        while (!feof($file)) {
            $city = fgetcsv($file, 0, ',');
            if (isset($city[0])) {
                $cities[] = trim($city[0]);
            }
        }
        fclose($file);

        if (count($cities)) {
            foreach ($cities as $city) {
                $city = City::create([
                    'name' => $city,
                    'country_id' => $state->country_id,
                    'state_id' => $state->id
                ]);

                DatingAddress::storeByCity($city);
            }
        }
       
        $request->session()->flash('admin_message_success', __('Cities has been successfully imported.'));

        return response()->json([
            'status' => true,
        ]);
    }
}