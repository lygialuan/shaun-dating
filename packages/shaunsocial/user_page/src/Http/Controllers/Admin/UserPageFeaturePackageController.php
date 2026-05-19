<?php


namespace Packages\ShaunSocial\UserPage\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\UserPage\Enum\UserPageFeaturePackageType;
use Packages\ShaunSocial\UserPage\Models\UserPageFeaturePackage;

class UserPageFeaturePackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.user_page.manage_feature_packages');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Profile Feature Packages'),
            ],
        ];
        $title = __('Profile Feature Packages');

        $packages = UserPageFeaturePackage::all();

        return view('shaun_user_page::admin.feature_package.index', compact('breadcrumbs', 'title', 'packages'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $package = new UserPageFeaturePackage();
            $package->is_active = true;
        } else {
            $package = UserPageFeaturePackage::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');
        $types = UserPageFeaturePackageType::getAll();

        return view('shaun_user_page::admin.feature_package.create', compact('title', 'package', 'types'));
    }

    public function store(Request $request)
    {
        $rules = [
            'name' => 'required',
            'amount' => ['required', new AmountValidation()],
            'type' => [
                'required', Rule::in(UserPageFeaturePackageType::values())
            ]
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'name.required' => __('The name is required.'),
                'name.max' => __('The name must not be greater than 255 characters.'),
                'amount.required' => __('The amount is required.'),
                'type.required' => __('The type is required.'),
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

        if ($request->id) {
            $package = UserPageFeaturePackage::findOrFail($request->id);
            $package->update($data);
        } else {
            UserPageFeaturePackage::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Package has been successfully updated.') : __('Package has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $package = UserPageFeaturePackage::findOrFail($id);

        $package->doDeleted();

        return redirect()->route('admin.user_page.feature_package.index')->with([
            'admin_message_success' => __('Package has been deleted.'),
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
            $package = UserPageFeaturePackage::find($id);
            if (! $package) {
                continue;
            }

            $package->update([
                'order' => $order + 1,
            ]);
        }

        return response()->json([
            'status' => true,
        ]);
    }
}
