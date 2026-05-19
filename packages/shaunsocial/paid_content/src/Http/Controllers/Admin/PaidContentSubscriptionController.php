<?php

namespace Packages\ShaunSocial\PaidContent\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Packages\ShaunSocial\Core\Http\Controllers\Controller;
use Packages\ShaunSocial\Core\Validation\AmountValidation;
use Packages\ShaunSocial\PaidContent\Enum\SubscriberPackageType;
use Packages\ShaunSocial\PaidContent\Models\SubscriberPackage;

class PaidContentSubscriptionController extends Controller
{

    public function __construct()
    {
        $this->middleware('has.permission:admin.paid_content.packages_manage');
    }

    public function package(Request $request)
    {
        $breadcrumbs = [
            [
                'title' => __('Dashboard'),
                'route' => 'admin.dashboard.index',
            ],
            [
                'title' => __('Subscriptions Packages'),
            ],
        ];
        $title = __('Subscriptions Packages');

        $builder = SubscriberPackage::orderBy('order')->orderBy('id','DESC');

        $type = $request->query('type', '');
        $typeArray = SubscriberPackageType::getAll();

        if (! in_array($type, array_keys($typeArray))) {
            $type = '';
        }

        if ($type) {
            $builder->where('type', $type);
        }

        $packages = $builder->paginate(setting('feature.item_per_page'));

        return view('shaun_paid_content::admin.subscription.package', compact('breadcrumbs', 'title', 'packages', 'type', 'typeArray'));
    }

    public function create_package($id = null)
    {
        if (empty($id)) {
            $package = new SubscriberPackage();
        } else {
            $package = SubscriberPackage::findOrFail($id);
        }

        $typeArray = SubscriberPackageType::getAll();

        $title = empty($id) ? __('Create') : __('Edit');

        return view('shaun_paid_content::admin.subscription.create_package', compact('title', 'package', 'typeArray'));
    }

    public function store_package(Request $request)
    {
        $rules = [
            'amount' => ['required', new AmountValidation()],
            'type' => [
                'required', Rule::in(SubscriberPackageType::values())
            ],
            'order'
        ];

        if ($request->id) {
            $package = SubscriberPackage::findOrFail($request->id);
            if (! $package->canChangeType()) {
                unset($rules['type']);
            }
        }

        $validation = Validator::make(
            $request->all(),
            $rules,
            [
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

        if (empty($data['order'])) {
            $data['order'] = 0;
        }

        if ($request->id) {
            if (! $package->canChangeType() && isset($data['type'])) {
                unset($data['type']);
            }
            $package->update($data);
        } else {
            SubscriberPackage::create($data);
        }

        $request->session()->flash('admin_message_success', $request->id ? __('Package has been successfully updated.') : __('Package has been created.'));

        return response()->json([
            'status' => true,
        ]);
    }

    public function delete_package($id)
    {
        $package = SubscriberPackage::findOrFail($id);

        $package->doDeleted();

        return redirect()->route('admin.paid_content.subscription.package')->with([
            'admin_message_success' => __('Package has been deleted.'),
        ]);
    }
}
