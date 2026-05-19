<?php

namespace Packages\ShaunSocial\PaidContent\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\PaidContent\Models\TipPackage;

class PaidContentTipController extends Controller
{

    public function __construct()
    {
        $this->middleware('has.permission:admin.paid_content.tips_manage');
    }

    public function package()
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Tips Packages'),
            ],
        ];
        $title = __('Tips Packages');

        $packages = TipPackage::orderBy('order', 'ASC')->orderBy('id', 'DESC')->get();

        return view('shaun_paid_content::admin.tip.package', compact('breadcrumbs', 'title', 'packages'));
    }

    public function create_package($id = null)
    {
        if (empty($id)) {
            $package = new TipPackage();
        } else {
            $package = TipPackage::findOrFail($id);
        }

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_paid_content::admin.tip.create_package', compact('title', 'package'));
    }

    public function store_package(Request $request)
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
            $package = TipPackage::findOrFail($request->id);

            $package->update($data);
        } else {
            TipPackage::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Package has been successfully updated.') : __('Package has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_package($id)
    {
        $package = TipPackage::findOrFail($id);

        $package->doDeleted();

        return redirect()->route('admin.paid_content.tip.package')->with([
            'admin_message_success' => __('Package has been deleted.'),
        ]);
    }

    public function store_order_package(Request $request)
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
            $package = TipPackage::find($id);
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
