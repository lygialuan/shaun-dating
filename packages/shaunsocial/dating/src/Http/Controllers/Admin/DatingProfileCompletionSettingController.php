<?php

namespace Packages\ShaunSocial\Dating\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Dating\Models\DatingProfileCompletionSetting;

class DatingProfileCompletionSettingController extends Controller
{

    public function __construct()
    {
        $this->middleware('has.permission:dating.manage_profile_completion_settings');
    }

    public function index(Request $request)
    {
        $title = __("Profile Completion Settings");

        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => $title
            ]
        ];

        $setting = DatingProfileCompletionSetting::findByField('id', 1);

        return view( 'shaun_dating::admin.profile_completion_settings.index', compact('breadcrumbs', 'title', 'setting'));
    }

    public function store(Request $request)
    {
        $fields = [
            'basic_info',
            'about',
            'profile_verification',
            'work_education',
            'more_about',
            'interests',
            'social_profiles',
        ];

        $validator = Validator::make(
            $request->all(),
            array_fill_keys($fields, 'nullable|integer|min:0|max:100')
        );

        $validator->after(function ($validator) use ($request, $fields) {
            $total = collect($fields)
                ->map(fn ($field) => (int) $request->input($field, 0))
                ->sum();

            if ($total !== 100) {
                $validator->errors()->add(
                    'total',
                    __('Total percentage must be 100%.')
                );
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status'   => false,
                'messages' => $validator->errors()->all(),
            ]);
        }

        $data = collect($fields)->mapWithKeys(fn ($field) => [$field => (int) $request->input($field, 0)])->toArray();

        $data['is_active'] = $request->boolean('is_active');

        DatingProfileCompletionSetting::updateOrCreate(['id' => DatingProfileCompletionSetting::first()?->id],$data);

        $request->session()->flash('admin_message_success',__('Profile completion settings saved successfully.'));

        return response()->json(['status' => true]);
    }
}
