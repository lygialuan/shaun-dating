<?php

namespace Packages\ShaunSocial\Wallet\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Wallet\Models\WalletPackage;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Validation\AmountValidation;

class WalletPackageController extends Controller
{
    public function __construct()
    {
        $this->middleware('has.permission:admin.wallet.package_manage');
    }

    public function index()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Deposit Packages'),
            ],
        ];
        $title = __('Deposit Packages');
        $packages = WalletPackage::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

        return view('shaun_wallet::admin.package.index', compact('breadcrumbs', 'title', 'packages'));
    }

    public function create($id = null)
    {
        if (empty($id)) {
            $package = new WalletPackage();
        } else {
            $package = WalletPackage::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_wallet::admin.package.create', compact('title', 'package'));
    }

    public function store(Request $request)
    {
        $rules = [
            'amount' => ['required', 'numeric', 'min:1', new AmountValidation()],
        ];

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
                'amount.required' => __('The amount is required.'),
                'amount.min' => __('The amount must be minimum 1.'),
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
            $package = WalletPackage::findOrFail($request->id);

            $package->update($data);
        } else {
            WalletPackage::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Package has been successfully updated.') : __('Package has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete($id)
    {
        $package = WalletPackage::findOrFail($id);

        $package->doDeleted();

        return redirect()->route('admin.wallet.package.index')->with([
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
            $package = WalletPackage::find($id);
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
