<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\Key;
use Packages\ShaunSocial\Core\Models\SmsProvider;
use Packages\ShaunSocial\Core\Models\StorageService;
use Throwable;

class SmsProviderController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.sms_provider.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Sms Providers'),
            ],
        ];
        $title = __('Sms Providers');
        $providers = SmsProvider::all();

        return view('shaun_core::admin.sms_provider.index', compact('breadcrumbs', 'title', 'providers'));
    }

    public function edit($id)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Sms Providers'),
                'route' => 'admin.sms_provider.index',
            ],
            [
                'title' => __('Edit Sms Provider'),
            ],
        ];
        $title = __('Edit Sms Provider');
        $provider = SmsProvider::findOrFail($id);

        return view('shaun_core::admin.sms_provider.edit', compact('title', 'provider', 'breadcrumbs'));
    }

    public function store(Request $request)
    {
        $provider = SmsProvider::findOrFail($request->id);

        $request->mergeIfMissing([
            'is_default' => 0,
        ]);

        $data = $request->except('id', '_token', 'key');
        $data['config'] = json_encode($data['config']);

        if ($provider->is_default) {
            $data['is_default'] = true;
        }

        if (! $provider->is_default && $data['is_default']) {
            SmsProvider::query()->update(['is_default' => 0]);
        }
        
        $provider->update($data);

        return redirect()->route('admin.sms_provider.index')->with([
            'admin_message_success' => __('Your changes have been saved.'),
        ]);
    }

    public function test($id)
    {
        $provider = SmsProvider::findOrFail($id);
        $title = __('Test Sms Provider');

        return view('shaun_core::admin.sms_provider.test', compact('id', 'title'));
    }

    public function store_test(Request $request)
    {
        $rules = [
            'phone_number' => 'required',
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The phone number is required.')
            ]
        );

        if ($validation->fails()) {
            return response()->json([
                'status' => false,
                'messages' => $validation->getMessageBag()->all(),
            ]);
        }

        $provider = SmsProvider::findOrFail($request->id);
        $result = $provider->sendSms('Test', $request->phone_number);

        if ($result['status']) {
            $request->session()->flash('admin_message_success', __('The text message sent successfully.'));
        } else {
            $result['messages'] = [$result['message']];
        }

        return response()->json($result);
    }
}
