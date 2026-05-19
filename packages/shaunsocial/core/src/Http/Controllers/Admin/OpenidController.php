<?php


namespace Packages\ShaunSocial\Core\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Models\OpenidProvider;
use Packages\ShaunSocial\Core\Validation\PhotoValidation;
use Packages\ShaunSocial\Core\Support\Facades\File;

class OpenidController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.openid.manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('OpenID Providers'),
            ],
        ];
        $title = __('OpenID Providers');
        $providers = OpenidProvider::orderBy('order')->orderBy('id', 'DESC')->get();

        return view('shaun_core::admin.openid.index', compact('breadcrumbs', 'title', 'providers'));
    }

    public function create($id = null,)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('OpenID Providers'),
                'route' => 'admin.openid.index',
            ],
        ];

        if (empty($id)) {
            $provider = new OpenidProvider();
            $breadcrumbs[] = [
                'title' => __('Create'),
            ];
        } else {
            $provider = OpenidProvider::findOrFail($id);
            $breadcrumbs[] = [
                'title' => __('Edit'),
            ];
        }
        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_core::admin.openid.create', compact('title', 'breadcrumbs', 'provider'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required|max:255',
            'app_name' => 'required|alpha_dash|max:128',
            'server' => 'required|max:255',
            'client_id' => 'required|max:255',
            'client_secret' => 'required|max:512',
            'authorize_endpoint' => 'required|max:255',
            'access_token_endpoint' => 'required|max:255',
            'get_user_info_endpoint' => 'required|max:255',
            'user_id_map' => 'required|max:255',
            'email_map' => 'max:255',
            'name_map' => 'required|max:255',
            'photo' => ['required',new PhotoValidation]
        ];

        if ($request->id) {
            $rules['photo'] = new PhotoValidation;
        }

        $request->validate(
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'app_name.required' => __('The app name is required.'),
                'app_name.max' => __('The app name must not be greater than 128 characters.'),
                'app_name.alpha_dash' => __('The app name must only contain letters, numbers, dashes and underscores.'),
                'server.required' => __('The server is required.'),
                'server.max' => __('The server must not be greater than 255 characters.'),
                'client_id.required' => __('The client ID is required.'),
                'client_id.max' => __('The client ID must not be greater than 255 characters.'),
                'client_secret.required' => __('The client secret is required.'),
                'client_secret.max' => __('The client secret must not be greater than 255 characters.'),
                'authorize_endpoint.required' => __('The authorize endpoint is required.'),
                'authorize_endpoint.max' => __('The authorize endpoint must not be greater than 255 characters.'),
                'access_token_endpoint.required' => __('The access token endpoint is required.'),
                'access_token_endpoint.max' => __('The access token must not be greater than 255 characters.'),
                'get_user_info_endpoint.required' => __('The get userinfo endpoint is required.'),
                'get_user_info_endpoint.max' => __('The get userInfo endpoint must not be greater than 255 characters.'),
                'user_id_map.required' => __('The user ID map is required.'),
                'user_id_map.max' => __('The user ID map endpoint must not be greater than 255 characters.'),
                'email_map.max' => __('The email map must not be greater than 255 characters.'),
                'name_map.required' => __('The name map is required.'),
                'name_map.max' => __('The name map must not be greater than 255 characters.'),
                'photo.required' => __('The logo is required.'),
                'photo.uploaded' => __('The logo is too large, maximum file size is :limit', ['limit' => getMaxUploadFileSize().'Kb']).'.'
            ]
        );
        
        $request->mergeIfMissing([
            'is_active' => 0
        ]);

        $data = $request->except('id', '_token');

        if ($request->id) {
            $provider = OpenidProvider::findOrFail($request->id);
            if ($provider->is_core) {
                unset($data['app_name']);
            }
            $provider->update($data);
        } else {
            $provider = OpenidProvider::create($data);
        }

        if ($request->hasFile('photo')) {
            $photo = $request->file('photo');

            $storageFile = File::storePhoto($photo, [
                'parent_id' => $provider->id,
                'parent_type' => 'openid_provider',
                'resize_size' => [
                    'width' => 300,
                    'height' => 300
                ]
            ]);

            $provider->update(['photo_id' => $storageFile->id]);
        }

        return redirect()->route('admin.openid.index')->with([
            'admin_message_success' => $request->id ? __('Provider has been successfully updated.') : __('Provider has been created.'),
        ]);
    }

    public function delete($id)
    {
        $provider = OpenidProvider::findOrFail($id);
        
        if ($provider->canDelete()) {
            $provider->delete();
        }

        return redirect()->route('admin.openid.index')->with([
            'admin_message_success' => __('Provider has been deleted.'),
        ]);
    }

    public function store_order(Request $request)
    {
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
            $provider = OpenidProvider::find($id);
            if (! $provider) {
                continue;
            }
            $provider->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
