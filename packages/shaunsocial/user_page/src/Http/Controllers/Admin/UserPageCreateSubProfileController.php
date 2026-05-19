<?php


namespace Packages\ShaunSocial\UserPage\Http\Controllers\Admin;

use Illuminate\Support\Str;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Role;
use Packages\ShaunSocial\Core\Models\Gender;
use Packages\ShaunSocial\Core\Models\Country;
use Packages\ShaunSocial\Dating\Repositories\Api\DatingRepository;
use Packages\ShaunSocial\UserPage\Models\UserPageCreateSubProfile;
use Packages\ShaunSocial\Core\Models\State;
use Packages\ShaunSocial\Core\Models\City;
use Packages\ShaunSocial\UserPage\Models\UserPageCreateSubProfileFakePhoto as FakePhoto;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;

class UserPageCreateSubProfileController extends Controller
{
    protected $datingRepository;

    public function __construct(DatingRepository $datingRepository)
    {
        $this->datingRepository = $datingRepository;

        $this->middleware('has.permission:admin.user_page.manage_create_sub_profile');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Create sub-profile'),
            ],
        ];
        $title = __('Create sub-profile');

        $roles = Role::getMemberRoles();

        $genders = Gender::getAllKeyValue();

        $countries = Country::getAll();

        $stats = FakePhoto::selectRaw("
            gender,
            COUNT(*) as total,
            SUM(CASE WHEN user_id > 0 THEN 1 ELSE 0 END) as used
        ")
        ->groupBy('gender')
        ->get()
        ->keyBy('gender');

        $maleTotal   = $stats['male']->total ?? 0;
        $femaleTotal = $stats['female']->total ?? 0;
        $maleUsed    = $stats['male']->used ?? 0;
        $femaleUsed  = $stats['female']->used ?? 0;

        return view('shaun_user_page::admin.create_sub_profile.index', compact('breadcrumbs', 'title', 'roles', 'genders', 'countries', 'maleTotal', 'femaleTotal', 'maleUsed', 'femaleUsed'));
    }

    public function state(Request $request)
    {
        $states = State::where('is_active', true)->where('country_id', $request->country_id)->orderBy('order')->orderBy('id', 'DESC')->get();

        return response()->json([
            'status' => true,
            'states' => $states
        ]);
    }

    public function city(Request $request)
    {
        $cities = City::where('is_active', true)->where('state_id', $request->state_id)->orderBy('order')->orderBy('id', 'DESC')->get();

        return response()->json([
            'status' => true,
            'cities' => $cities
        ]);
    }

    public function interest(Request $request)
    {
        $title =  __('Interests');
        
        $attributesInterest = $this->datingRepository->getInterestAttributes();

        return view('shaun_user_page::admin.create_sub_profile.interest', compact('title', 'attributesInterest'));
    }
    
    public function more_about_me(Request $request)
    {
        $title =  __('More about me');

        $attributesMoreAbout = $this->datingRepository->getAttributes();

        return view('shaun_user_page::admin.create_sub_profile.more_about_me', compact('title', 'attributesMoreAbout'));
    }

    public function store(Request $request)
    {
        $rules = [
            'number_of_users' => 'required|integer|min:1|max:50',
            'expire_role_id'  => 'required|exists:roles,id',
            'gender_id'       => 'required|integer',
            'from_age'        => 'required|integer|min:18',
            'to_age'          => 'required|integer|max:80|gte:from_age',
            'country_id'      => 'required|integer',
            'about_me'        => 'required|string',
            'interests'       => 'required|string',
            'user_id'         => 'required|integer',
        ];

        if ($request->hasFile('photos')) {
            $rules['photos'] = 'array|max:10';
            $rules['photos.*'] = [new PhotoValidation()];
        }

        $messages = [
            'number_of_users.required' => __('Number of users is required.'),
            'number_of_users.max'      => __('Number of users cannot exceed 50.'),
            'expire_role_id.required'  => __('User role is required.'),
            'from_age.required'        => __('From age is required.'),
            'to_age.required'          => __('To age is required.'),
            'to_age.gte'               => __('To age must be greater than or equal to From age.'),
            'user_id.required'         => __('Sub-profile of user is required.'),
            'country_id.required'      => __('Country is required.'),
            'photos'                   => 'nullable|array|max:10',
            'photos.uploaded'          => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validator->errors()->all(),
            ]);
        }

        $data = $request->only([
            'number_of_users',
            'expire_role_id',
            'gender_id',
            'from_age',
            'to_age',
            'about_me',
            'interests',
            'user_id',
            'country_id',
            'state_id',
            'city_id',
        ]);

        $data['about_me'] = $request->input('about_me') ? json_encode(json_decode($request->input('about_me'))) : null;
        $data['interests'] = $request->input('interests') ? json_encode(json_decode($request->input('interests'))) : null;

        UserPageCreateSubProfile::create($data);

        if ($request->hasFile('photos')) {
            $gender = $request->gender_id == 1 ? 'male' : 'female';
            $prefix = substr($gender, 0, 1);
            foreach ($request->file('photos') as $file) {
                $filename = $prefix . '_' . time() . '_' . Str::random(6) . '.' . $file->extension();
                $destinationPath = public_path("images/default/photo/{$gender}");
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                FakePhoto::create([
                    'gender'  => $gender,
                    'photo'   => $filename,
                    'user_id' => 0
                ]);
            }
        }

        $request->session()->flash('admin_message_success', __('Fake users are being created. This may take a few minutes.'));

        return response()->json([
            'status' => true,
        ]);
    }
    
    public function upload_photos(Request $request)
    {
        $genders = Gender::getAllKeyValue();

        return view('shaun_user_page::admin.create_sub_profile.upload_photos',  compact('genders'));
    }

    public function store_upload_photos(Request $request)
    {
        $rules = [
            'gender_id' => 'required|integer',
        ];

        if ($request->hasFile('photos')) {
            $rules['photos'] = 'array|max:10';
            $rules['photos.*'] = [new PhotoValidation()];
        }

        $messages = [
            'photos'          => 'nullable|array|max:10',
            'photos.uploaded' => __('The file is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
        ];

        $validator = Validator::make($request->all(), $rules, $messages);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validator->errors()->all(),
            ]);
        }

        if ($request->hasFile('photos')) {
            $gender = $request->gender_id == 1 ? 'male' : 'female';
            $prefix = substr($gender, 0, 1);
            foreach ($request->file('photos') as $file) {
                $filename = $prefix . '_' . time() . '_' . Str::random(6) . '.' . $file->extension();
                $destinationPath = public_path("images/default/photo/{$gender}");
                if (!file_exists($destinationPath)) {
                    mkdir($destinationPath, 0755, true);
                }
                $file->move($destinationPath, $filename);
                FakePhoto::create([
                    'gender'  => $gender,
                    'photo'   => $filename,
                    'user_id' => 0
                ]);
            }
        }

        $request->session()->flash('admin_message_success', __('Photos has been successfully uploaded.'));

        return response()->json([
            'status' => true,
        ]);
    }
}
